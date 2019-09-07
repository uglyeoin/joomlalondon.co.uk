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

namespace RegularLabs\Plugin\System\ArticlesAnywhere\Components\K2\Output\Data;

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table as JTable;
use K2ModelItem;
use RegularLabs\Library\RegEx as RL_RegEx;

class Extra extends \RegularLabs\Plugin\System\ArticlesAnywhere\Output\Data\Extra
{
	public function get($key, $attributes)
	{
		if (RL_RegEx::match('^extra-([0-9]+)$', $key, $match))
		{
			return $this->getExtraFieldsValueByID($match[1], $attributes);
		}

		return parent::get($key, $attributes);
	}

	private function getExtraFieldsValueByID($id, $attributes)
	{
		$extra_fields = $this->item->getGroupValues('extra_fields');

		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_k2/tables');

		if ( ! class_exists('K2ModelItem'))
		{
			require_once JPATH_SITE . '/components/com_k2/models/item.php';
		}

		$item  = $this->item->get();
		$model = new K2ModelItem;

		$fields = $model->getItemExtraFields(json_encode($extra_fields), $item);

		foreach ($fields as $field)
		{
			if ($field->id != $id)
			{
				continue;
			}

			if ( ! $field->published)
			{
				return '';
			}

			$show_label = isset($attributes->label) ? $attributes->label : false;

			if ($show_label === 'only')
			{
				return $field->name;
			}

			$value = $field->value;

			if (isset($attributes->output)
				&& in_array($attributes->output, ['value', 'values', 'raw']))
			{
				foreach ($extra_fields as $extra_field)
				{
					if ($field->id != $extra_field->id)
					{
						continue;
					}

					$value = $extra_field->value;
					break;
				}
			}

			if ( ! $show_label)
			{
				return $value;
			}

			$format = isset($attributes->format) ? $attributes->format : '%s: %s';

			return sprintf($format, $field->name, $value);
		}

		return '';
	}

}
