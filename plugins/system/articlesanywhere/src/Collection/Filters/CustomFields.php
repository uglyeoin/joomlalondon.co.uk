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

namespace RegularLabs\Plugin\System\ArticlesAnywhere\Collection\Filters;

defined('_JEXEC') or die;


use JDatabaseQuery;
use RegularLabs\Library\ArrayHelper as RL_Array;
use RegularLabs\Library\DB as RL_DB;
use RegularLabs\Plugin\System\ArticlesAnywhere\Collection\DB;

class CustomFields extends Fields
{
	public function setFilter(JDatabaseQuery $query, $filters = [])
	{
		foreach ($filters as $id => $values)
		{
			$item_ids = $this->getIdGroupds($id, $values);

			if (is_array($item_ids->include))
			{
				$query->where($this->db->quoteName('items.id') . RL_DB::in($item_ids->include));
			}

			if ( ! empty($item_ids->exclude))
			{
				$item_ids->exclude[0] = '!' . $item_ids->exclude[0];

				$query->where($this->db->quoteName('items.id') . RL_DB::in($item_ids->exclude));
			}
		}
	}

	protected function getItemIdsByFieldIds($ids)
	{
		if (empty($ids))
		{
			return [];
		}

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('content_item_id'))
			->from($this->db->quoteName('#__contentitem_tag_map'))
			->where($this->db->quoteName('type_alias') . ' = ' . $this->db->quote('com_content.article'))
			->where($this->db->quoteName('tag_id') . RL_DB::in($ids))
			->group($this->db->quoteName('content_item_id'));

		return DB::getResults($query) ?: [];
	}

	protected function getIdGroupds($id, $values)
	{
		$includes = [];
		$excludes = [];

		$values = RL_Array::toArray($values);

		if (empty($values))
		{
			$values = [''];
		}

		foreach ($values as $value)
		{
			if ($value == '')
			{
				$value = '!*';
			}

			if ($value == '!' || $value == '!NOT!')
			{
				$value = '+';
			}

			$operator = RL_DB::getOperatorFromValue($value);
			$value    = RL_DB::removeOperator($value);

			if ($operator == '!=')
			{
				$excludes[$id] = isset($excludes[$id]) ? $excludes[$id] : [];

				$excludes[$id][] = $value;
				continue;
			}

			$includes[$id]   = isset($includes[$id]) ? $includes[$id] : [];
			$includes[$id][] = $operator . $value;
		}

		$include = $this->getFieldIdsByValues($includes);
		$exclude = $this->getFieldIdsByValues($excludes);

		return (object) compact('include', 'exclude');
	}

	protected function getFieldIdsByValues($values)
	{
		if (empty($values))
		{
			return null;
		}

		$conditions = [];

		foreach ($values as $field_id => $value)
		{
			$conditions[] = '('
				. $this->db->quoteName('field_id') . ' = ' . $this->db->quote($field_id)
				. ' AND ' . $this->getConditionsFromValues('value', $value, [])
				. ')';
		}

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('item_id'))
			->from($this->config->getTableFieldsValues())
			->where($conditions, 'OR');

		return DB::getResults($query) ?: [];
	}
}
