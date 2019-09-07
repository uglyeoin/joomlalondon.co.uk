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
use RegularLabs\Library\DB as RL_DB;
use RegularLabs\Plugin\System\ArticlesAnywhere\Collection\DB;

class Categories extends Filter
{
	public function setFilter(JDatabaseQuery $query, $filters = [])
	{
		$exclude = false;

		if (isset($filters[0]))
		{
			$exclude    = RL_DB::getOperatorFromValue($filters[0]) == '!=';
			$filters[0] = RL_DB::removeOperator($filters[0]);
		}

		$ids = $this->getIds($filters);

		if (empty($ids))
		{
			$query->where($exclude ? '1' : '0');

			return;
		}

		if ($exclude)
		{
			$ids[0] = '!=' . $ids[0];
			$ids    = $this->getIds($ids);
		}

		$query->where($this->db->quoteName('items.catid') . RL_DB::in($ids));
	}

	private function getIds($names = [])
	{
		$query = $this->getIdsQuery($names);

		$ids = DB::getResults($query) ?: [];

		if ( ! $include_children = $this->config->getFiltersIncludeChildren('categories'))
		{
			return $ids;
		}

		$max_depth = is_int($include_children) ? $include_children : 20;
		$depth     = 0;

		$child_ids = $ids;

		while ($depth++ < $max_depth && $child_ids = $this->getChildIds($child_ids))
		{
			$ids = array_merge($ids, $child_ids);
		}

		return $ids;
	}

	private function getChildIds($ids = [])
	{
		$query = $this->getChildIdsQuery($ids);

		return DB::getResults($query) ?: [];
	}

	private function getIdsQuery($names = [])
	{
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('id'))
			->from($this->config->getTableCategories('categories'));

		if ($extension = $this->config->get('categories_extension', false))
		{
			$query->where($this->db->quoteName('extension') . ' = ' . $this->db->quote($extension));
		}

		$this->setFiltersFromNames($query, 'categories', $names);

		$this->setIgnores($query, 'categories', 'categories');

		return $query;
	}

	private function getChildIdsQuery($parent_ids = [])
	{
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('id'))
			->from($this->config->getTableCategories('categories'))
			->where($this->config->get('categories_parent_id')
				. RL_DB::in($parent_ids));

		if ($extension = $this->config->get('categories_extension', false))
		{
			$query->where($this->db->quoteName('extension') . ' = ' . $this->db->quote($extension));
		}

		return $query;
	}
}
