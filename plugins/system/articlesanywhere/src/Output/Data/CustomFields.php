<?php
/**
 * @package         Articles Anywhere
 * @version         9.3.5PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2019 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\ArticlesAnywhere\Output\Data;

defined('_JEXEC') or die;


use FieldsHelper;
use JEventDispatcher;
use JLoader;
use Joomla\CMS\HTML\HTMLHelper as JHtml;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Plugin\PluginHelper as JPluginHelper;
use Joomla\Registry\Registry;
use RegularLabs\Library\ArrayHelper as RL_Array;
use RegularLabs\Plugin\System\ArticlesAnywhere\Collection\Fields\CustomFields as Fields;
use RegularLabs\Plugin\System\ArticlesAnywhere\Collection\Item;
use RegularLabs\Plugin\System\ArticlesAnywhere\Config;
use RegularLabs\Plugin\System\ArticlesAnywhere\Factory;
use RegularLabs\Plugin\System\ArticlesAnywhere\Helpers\ValueHelper;
use RegularLabs\Plugin\System\ArticlesAnywhere\Output\Values;

class CustomFields extends Data
{
	/* @var Text $text */
	private $text;
	/* @var Images $images */
	private $images;

	private $fields = [];

	public function __construct(Config $config, Item $item, Values $values)
	{
		parent::__construct($config, $item, $values);

		JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');

		$this->text   = Factory::getOutput('Text', $config, $item, $values);
		$this->images = Factory::getOutput('Images', $config, $item, $values);
		$this->fields = FieldsHelper::getFields('com_content.article', $this->item->get());
	}

	public function get($key, $attributes)
	{
		$fields = (new Fields($this->config))->getFieldByKey($key, $this->item->getId());

		if (empty($fields))
		{
			return null;
		}

		$label       = JText::_($fields[0]->label);
		$params      = json_decode(isset($fields[0]->params) ? $fields[0]->params : '{}');
		$fieldparams = json_decode(isset($fields[0]->fieldparams) ? $fields[0]->fieldparams : '{}');

		$layout    = isset($attributes->layout) ? $attributes->layout : '';
		$showlabel = isset($attributes->showlabel)
			? $attributes->showlabel
			: (! empty($attributes->layout) && isset($params->showlabel) ? $params->showlabel : false);

		if ( ! empty($params->layout)
			&& (empty($attributes->layout) || $attributes->layout === true || $attributes->layout == 'true'))
		{
			$layout = 'field.' . $params->layout;
			if ( ! isset($attributes->showlabel) && ! empty($attributes->layout) && isset($params->showlabel))
			{
				$showlabel = $params->showlabel;
			}
		}

		$attributes->layout    = $layout;
		$attributes->showlabel = $showlabel;

		if ($attributes->showlabel === 'only')
		{
			return $label;
		}

		if ($fields[0]->type == 'repeatable')
		{
			$this->prepareRepeatableField($fields, $attributes);
		}

		// Should we combine attributes with the $fieldparams ?

		$value = $this->getValue($fields, $attributes);

		if ($this->values->isDateValue($key, $value))
		{
			if ( ! isset($attributes->showtime) && isset($fieldparams->showtime))
			{
				$attributes->showtime = $fieldparams->showtime;
			}
			$attributes->is_custom_field = true;
		}

		return $this->getOutput($label, $value, $attributes);
	}

	private function prepareRepeatableField($fields, $attributes)
	{
		$keys = ! empty($attributes->fields) ? RL_Array::toArray($attributes->fields) : [];

		foreach ($fields as &$field)
		{
			if (empty($field->value))
			{
				continue;
			}

			$value = json_decode($field->value);

			if (empty($value))
			{
				continue;
			}

			$fieldparams = json_decode($field->fieldparams);

			$types = [];

			foreach ($fieldparams->fields as $key => $fieldparam)
			{
				if ( ! empty($keys) && ! in_array($fieldparam->fieldname, $keys))
				{
					unset($fieldparams->fields->{$key});
					continue;
				}

				$types[$fieldparam->fieldname] = $fieldparam->fieldtype;
			}

			foreach ($value as $key => &$row)
			{
				if ( ! empty($keys))
				{
					// Filter and order keys in order of given
					$row = array_merge(
						array_flip($keys),
						array_intersect_key(
							(array) $row,
							array_flip($keys)
						)
					);
				}

				foreach ($row as $key => &$val)
				{
					if ($types[$key] != 'media')
					{
						continue;
					}

					// TODO: This doesn't work correctly yet
					// Figure out what data to pass on
					return $this->getItemValueMedia($field, $attributes);
				}
			}

			$field->value       = json_encode($value);
			$field->fieldparams = json_encode($fieldparams);
		}
	}

	private function getValue($fields, $attributes)
	{
		$values = [];

		foreach ($fields as &$field)
		{
			$values[$field->value] = $this->getItemValue($field, $attributes);
		}

		$values = RL_Array::removeEmpty($values);

		$value_layout = isset($attributes->value_layout)
			? $attributes->value_layout
			: (isset($attributes->values_layout)
				? $attributes->values_layout
				: null);

		if ($value_layout)
		{
			$value_layout = $this->getLayoutPathDotted($value_layout);
			$displayData  = [
				'values'     => $values,
				'attributes' => $attributes,
			];

			$value_layout = new FileLayout($value_layout, JPATH_SITE, ['component' => 'com_fields', 'client' => 0]);

			$output_success = true;
			$output         = $value_layout->render($displayData);

			foreach ($value_layout->getDebugMessages() as $message)
			{
				if (strpos($message, 'Unable to find layout') !== false)
				{
					$output_success = false;
					break;
				}
			}

			if ($output_success)
			{
				return $output;
			}
		}

		$separator = isset($attributes->separator) ? $attributes->separator : (isset($attributes->delimiter) ? $attributes->delimiter : ', ');

		return implode($separator, $values);
	}

	private function getLayoutPathDotted($path, $prefix = 'field')
	{
		$path = str_replace('.php', '', $path);
		$path = str_replace('/', '.', trim($path, '/'));

		if (strpos($path, '.') === false)
		{
			$path = $prefix . '.' . $path;
		}

		return $path;
	}

	private function getItemValue($field, $attributes)
	{
		if (empty($field->value))
		{
			return '';
		}

		if (in_array($field->type, ['text', 'textarea', 'editor']))
		{
			return $this->text->process($field->value, $attributes);
		}

		if (empty($field->fieldparams))
		{
			return $field->value;
		}

		$params = json_decode($field->fieldparams);

		if ( ! empty($params->options))
		{
			return $this->getItemValueByOptions($field, $params->options, $attributes);
		}

		return $this->getValueFromField($field, $attributes);
	}

	private function getValueFromField($field, $attributes)
	{
		$value = $field->value;

		// Do not use the raw output if the $attributes value is set to 'output' or 'render'
		if (isset($attributes->value)
			&& in_array($attributes->value, ['output', 'render']))
		{
			unset($attributes->output);
			unset($attributes->value);
		}

		if (isset($attributes->output)
			&& in_array($attributes->output, ['value', 'values', 'raw']))
		{
			return $value;
		}

		if (in_array($field->type, ['media']))
		{
			return $this->getItemValueMedia($field, $attributes);
		}

		if ( ! $field_object = $this->getFieldObject($field->id))
		{
			return $value;
		}

		$field_object->value = $field->value;

		if (isset($attributes->format) && ValueHelper::isDateValue($value))
		{
			return JHtml::_('date', $value, 'Y-m-d H:i:s');
		}

		if ( ! JPluginHelper::importPlugin('fields', $field->type))
		{
			return $value;
		}

		$plugin_class = 'PlgFields' . ucfirst($field->type);

		$dispatcher = JEventDispatcher::getInstance();
		$params     = (array) JPluginHelper::getPlugin('fields', $field->type);
		$plugin     = new $plugin_class($dispatcher, $params);

		if (method_exists($plugin, 'onCustomFieldsBeforePrepareField'))
		{
			$plugin->onCustomFieldsBeforePrepareField('com_content.article', $this->item->get(), $field_object);
		}

		$value = $plugin->onCustomFieldsPrepareField('com_content.article', $this->item->get(), $field_object);

		if (is_array($value))
		{
			$value = implode(' ', $value);
		}

		if (method_exists($plugin, 'onCustomFieldsAfterPrepareField'))
		{
			$plugin->onCustomFieldsAfterPrepareField('com_content.article', null, $field_object, $value);
		}

		return $value;
	}

	private function getFieldObject($field_id)
	{
		foreach ($this->fields as $field)
		{
			if ($field->id == $field_id)
			{
				return $field;
			}
		}

		return false;
	}

	private function getItemValueByOptions($field, $options, $attributes)
	{
		foreach ($options as $option)
		{
			if ($field->value != $option->value)
			{
				continue;
			}

			if (isset($attributes->output)
				&& in_array($attributes->output, ['value', 'values', 'raw']))
			{

				return $option->value;
			}

			return JText::_($option->name);
		}

		return $field->value;
	}

	private function getOutput($label, $value, $attributes)
	{
		// Don't output anything if value is empty
		if (empty($value))
		{
			return '';
		}

		$attributes->showlabel = isset($attributes->showlabel) ? (bool) $attributes->showlabel : false;

		$field = (object) [
			'label'     => $label,
			'value'     => $value,
			'raw_value' => $value,
			'params'    => new Registry($attributes),
		];

		$result_from_layout = $this->getOutputViaLayout($field, $attributes);

		if ($result_from_layout !== false)
		{
			return $result_from_layout;
		}

		if ( ! $attributes->showlabel)
		{
			return $value;
		}

		$format = isset($attributes->format) ? $attributes->format : '%s: %s';

		return sprintf($format, $label, $value);
	}

	/**
	 * @param $field
	 * @param $params
	 *
	 * @return string
	 */
	private function getOutputViaLayout($field, $attributes)
	{
		if (empty($attributes->layout))
		{
			return false;
		}

		if (isset($attributes->output)
			&& in_array($attributes->output, ['value', 'values', 'raw']))
		{
			return false;
		}

		$attributes->layout = ($attributes->layout === true || $attributes->layout == 'true' || $attributes->layout == 'default')
			? 'field.render'
			: $this->getLayoutPathDotted($attributes->layout);

		$field->params = new Registry($attributes);

		$displayData = [
			'field'      => $field,
			'attributes' => $attributes,
		];

		$layout = new FileLayout($attributes->layout, JPATH_SITE, ['component' => 'com_fields', 'client' => 0]);

		$output = $layout->render($displayData);

		foreach ($layout->getDebugMessages() as $message)
		{
			if (strpos($message, 'Unable to find layout') !== false)
			{
				$attributes->layout = 'field.render';
				$layout             = new FileLayout($attributes->layout, JPATH_SITE, ['component' => 'com_fields', 'client' => 0]);

				$output = $layout->render($displayData);
				break;
			}
		}

		return $output;
	}

	private function getItemValueMedia($field, $attributes)
	{
		if ( ! empty($attributes->clean))
		{
			return $field->value;
		}

		$params = json_decode($field->fieldparams);

		$image = $this->images->getImageByUrl($field->value, $attributes);

		$attributes->src   = $image['url'];
		$attributes->class = isset($attributes->class)
			? $attributes->class
			: (isset($params->image_class) ? $params->image_class : '');
		Images::setAltAndTitle('custom_fields', $attributes, $field);

		$image_attributes = clone $attributes;

		unset($image_attributes->clean);
		unset($image_attributes->layout);
		unset($image_attributes->value_layout);
		unset($image_attributes->showlabel);

		return $this->images->getImageHtml($image_attributes);
	}
}
