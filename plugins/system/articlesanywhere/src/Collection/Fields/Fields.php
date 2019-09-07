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

namespace RegularLabs\Plugin\System\ArticlesAnywhere\Collection\Fields;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use RegularLabs\Library\ArrayHelper as RL_Array;
use RegularLabs\Library\RegEx as RL_RegEx;
use RegularLabs\Plugin\System\ArticlesAnywhere\Collection\CollectionObject;
use RegularLabs\Plugin\System\ArticlesAnywhere\CurrentArticle;
use RegularLabs\Plugin\System\ArticlesAnywhere\Params;

class Fields extends CollectionObject implements FieldInterface
{
	static $available_fields = [];
	static $fields           = [];
	static $field_types      = [];
	static $field_values     = [];

	public function getAvailableFields()
	{
		$id = $this->config->getTableItems(false);

		if (isset(self::$available_fields[$id]))
		{
			return self::$available_fields[$id];
		}

		self::$available_fields[$id] = array_keys(JFactory::getDbo()->getTableColumns($this->config->getTableItems(false)));

		return self::$available_fields[$id];
	}

	public function getFieldValue($key, $value)
	{
		$current_value = CurrentArticle::get($key, $this->config->getComponentName());

		return $this->getValue($key, $value, $current_value);
	}

	protected function getValue($key, $value, $current_value)
	{
		if ($this->isCurrentValue($value, $key))
		{
			return $current_value;
		}

		if (is_array($current_value))
		{
			return $this->getArrayValue($value, $current_value);
		}

		// It's a current article value [this:id], [this:title], etc
		if (RL_RegEx::match('^this:([a-z0-9_\-]+)$', $value, $match))
		{
			return CurrentArticle::get($match[1]);
		}

		// It's a a user value [user:id], [user:name], etc
		if (RL_RegEx::match('^user:([a-z0-9_\-]+)$', $value, $match))
		{

			return JFactory::getUser()->get($match[1]);
		}

		// It's an input value [input:id], [input:name:default], etc
		if (RL_RegEx::match('^input:([^"]+)$', $value, $match))
		{
			list($value, $default) = explode(':', $match[1] . ':none');

			return JFactory::getApplication()->input->getString($value, $default);
		}

		return $this->getSimpleValue($value, $current_value);
	}

	protected function getSimpleValue($value, $current_value)
	{
		if (is_bool($current_value))
		{
			return (bool) $value;
		}

		if (is_bool($value))
		{
			return (int) $value;
		}

		return $value;
	}

	protected function getArrayValue($value, $current_value)
	{
		if (is_array($value))
		{
			return $value;
		}

		return explode(',', $value);
	}

	protected function isCurrentValue($value, $key)
	{
		$values_equaling_current = $this->getCurrentValueTags($key);

		return in_array($value, $values_equaling_current, true);
	}

	protected function getCurrentValueTags($keys = [])
	{
		$tag_chars = Params::getDataTagCharacters();

		$keys   = RL_Array::toArray($keys);
		$keys   = RL_Array::clean($keys);
		$keys[] = 'current';

		array_walk($keys, function (&$key, $count, $tag_chars) {
			$key = $tag_chars[0] . $key . $tag_chars[1];
		}, $tag_chars);

		$keys[] = 'current';

		return $keys;
	}
}
