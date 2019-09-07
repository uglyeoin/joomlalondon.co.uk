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

class Tags extends Filter
{
	var $names;

	public function setFilter(JDatabaseQuery $query, $filters = [])
	{
		$this->names = $filters;

		$tag_ids = $this->getTagIds();

		$include = $this->getItemIdsByTagIds($tag_ids->include);
		$exclude = $this->getItemIdsByTagIds($tag_ids->exclude);

		if (empty($include) && empty($exclude))
		{
			$query->where('0');

			return false;
		}

		if ( ! empty($include))
		{
			$query->where($this->db->quoteName('items.id') . RL_DB::in($include));
		}

		if ( ! empty($exclude))
		{
			$exclude[0] = '!' . $exclude[0];
			$query->where($this->db->quoteName('items.id') . RL_DB::in($exclude));
		}

		return true;
	}

	public function setConditionsWhenEmpty(JDatabaseQuery $query)
	{
		$tag_ids       = DB::getResults($this->getTagsIdsQuery(['*']), 'loadColumn');
		$ids_with_tags = $this->getItemIdsByTagIds($tag_ids);

		$ids_with_tags[0] = '!' . $ids_with_tags[0];

		$query->where($this->db->quoteName('items.id') . RL_DB::in($ids_with_tags));
	}

	protected function getItemIdsByTagIds($ids)
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

	protected function getTagIds()
	{
		$includes = [];
		$excludes = [];

		foreach ($this->names as $name)
		{
			if ($name == '!' || $name == '!NOT!')
			{
				$name = '+';
			}

			$operator = RL_DB::getOperatorFromValue($name);
			$name     = RL_DB::removeOperator($name);

			if ($operator == '!=')
			{
				$excludes[] = $name;
				continue;
			}

			$includes[] = $name;
		}

		$include = ! empty($includes) ? DB::getResults($this->getTagsIdsQuery($includes), 'loadColumn') : [];
		$exclude = ! empty($excludes) ? DB::getResults($this->getTagsIdsQuery($excludes), 'loadColumn') : [];

		return (object) compact('include', 'exclude');
	}

	protected function getTagsIdsQuery($names)
	{
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('id'))
			->from($this->config->getTableTags('tags'));

		$this->setFiltersFromNames($query, 'tags', $names);
		$this->setIgnores($query, 'tags', 'tags');

		return $query;
	}
}
