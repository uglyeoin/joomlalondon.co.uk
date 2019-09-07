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

namespace RegularLabs\Plugin\System\ArticlesAnywhere\Collection;

defined('_JEXEC') or die;

use RegularLabs\Library\DB as RL_DB;
use RegularLabs\Plugin\System\ArticlesAnywhere\Collection\Filters;
use RegularLabs\Plugin\System\ArticlesAnywhere\Config;
use RegularLabs\Plugin\System\ArticlesAnywhere\Factory;
use RegularLabs\Plugin\System\ArticlesAnywhere\Output\Output;
use RegularLabs\Plugin\System\ArticlesAnywhere\Params;

class Collection extends CollectionObject
{
	/* @var Filters\Items $items */
	protected $items;
	/* @var Filters\Fields $fields */
	protected $fields;
	/* @var Filters\Categories $categories */
	protected $categories;
	/* @var Filters\Tags $tags */
	protected $tags;
	/* @var Filters\CustomFields $custom_fields */
	protected $custom_fields;

	public function __construct(Config $config)
	{
		parent::__construct($config);

		$this->items      = Factory::getFilter('Items', $config);
		$this->fields     = Factory::getFilter('Fields', $config);
		$this->pagination = Factory::getPagination($config);

		$this->categories    = Factory::getFilter('Categories', $config);
		$this->tags          = Factory::getFilter('Tags', $config);
		$this->custom_fields = Factory::getFilter('CustomFields', $config);
	}

	public function getOnlyIds()
	{
		return $this->getIds();
	}

	public function getOutputByIds($total_ids = [], $default = '')
	{
		if (empty($total_ids))
		{
			$surrounding_tags = $this->config->getData('surrounding_tags');

			return $surrounding_tags->opening
				. $default
				. $surrounding_tags->closing;
		}

		$ids = $this->applyLimit($total_ids);

		// Now get Item data for found ids
		$items = $this->getData($ids);

		$items = array_map(function ($item) {
			return Factory::getItem($this->config, $item);
		}, $items);

		return $this->getOutput(
			$items,
			count($total_ids),
			count($ids)
		);
	}

	protected function getOutput($items, $total_no_limit, $total_no_pagination)
	{
		return (new Output($this->config))->get($items, $total_no_limit, $total_no_pagination);
	}

	protected function getIds()
	{
		$query = $this->getIdsQuery();

		return DB::getResults($query) ?: [];
	}

	protected function getIdsQuery()
	{
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('items.id'))
			->from($this->config->getTableItems('items'))
			->group($this->db->quoteName('items.id'));

		$this->items->set($query);
		$this->fields->set($query);
		$this->categories->set($query);
		$this->tags->set($query);
		$this->custom_fields->set($query);

		if ($this->config->getFilters('one_per_category'))
		{
			$query->clear('group')->group($this->db->quoteName('items.catid'));
		}

		$this->setIgnores($query);

		return $query;
	}

	protected function applyLimit($ids)
	{
		if (empty($ids))
		{
			return [];
		}

		$query = $this->getDataQuery($ids);

		if ( ! $query)
		{
			return [];
		}

		$result = DB::getResults($query,
			'loadObjectList',
			['id'],
			$this->pagination->params->total_limit,
			$this->pagination->params->offset_start
		);

		return array_keys($result);
	}

	protected function getData($ids)
	{
		$query = $this->getDataQuery($ids);

		if ( ! $query)
		{
			return [];
		}

		$query->select('items.*');

		return DB::getResults($query,
			'loadObjectList',
			[],
			$this->pagination->params->limit,
			$this->pagination->params->offset - $this->pagination->params->offset_start
		);
	}

	protected function getDataQuery($ids = [])
	{
		if (empty($ids))
		{
			return false;
		}

		$selects = $this->config->getSelects();

		$query = $this->db->getQuery(true)
			->select('items.id')
			->from($this->config->getTableItems('items'))
			->where($this->db->quoteName('items.id') . RL_DB::in($ids))
			->group($this->db->quoteName('items.id'));

		if ($selects['frontpage'])
		{
			$query->select([
				$this->db->quoteName('frontpage.ordering', 'featured-ordering'),
			])
				->join('LEFT', $this->config->getTableFeatured('frontpage')
					. ' ON ' . $this->db->quoteName('frontpage.content_id') . ' = ' . $this->db->quoteName('items.id'));
		}

		if ($selects['categories'])
		{
			$query->select([
				$this->config->getId('categories', 'category-id', 'categories'),
				$this->config->getTitle('categories', 'category-title', 'categories'),
				$this->config->getAlias('categories', 'category-alias', 'categories'),
				$this->config->get('description', 'category-description', 'categories', 'description'),
				$this->db->quoteName('categories.params', 'category-params'),
				//$this->db->quoteName('categories.metadata', 'category-metadata'),
			])
				->join('LEFT', $this->config->getTableCategories('categories')
					. ' ON ' . $this->db->quoteName('categories.id') . ' = ' . $this->db->quoteName('items.catid'));
		}

		if ($selects['users'])
		{
			$query->select([
				$this->db->quoteName('user.id', 'author-id'),
				$this->db->quoteName('user.name', 'author-name'),
				$this->db->quoteName('user.username', 'author-username'),
			])
				->join('LEFT', $this->db->quoteName('#__users', 'user')
					. ' ON ' . $this->db->quoteName('user.id') . ' = ' . $this->db->quoteName('items.created_by'));
		}

		if ($selects['modifiers'])
		{
			$query->select([
				$this->db->quoteName('modifier.id', 'modifier-id'),
				$this->db->quoteName('modifier.name', 'modifier-name'),
				$this->db->quoteName('modifier.username', 'modifier-username'),
			])
				->join('LEFT', $this->db->quoteName('#__users', 'modifier')
					. ' ON ' . $this->db->quoteName('modifier.id') . ' = ' . $this->db->quoteName('items.modified_by'));
		}

		if ($selects['custom_fields'])
		{
			foreach ($selects['custom_fields'] as $custom_field)
			{
				$table_as = 'custom_field_' . $custom_field->id;

				$query->select($this->db->quoteName($table_as . '.value', 'custom_field_' . $custom_field->name))
					->join('LEFT', $this->config->getTableFieldsValues($table_as)
						. "\n" . ' ON ' . $this->db->quoteName($table_as . '.item_id') . ' = ' . $this->db->quoteName('items.id')
						. "\n" . ' AND ' . $this->db->quoteName($table_as . '.field_id') . ' = ' . $this->db->quote($custom_field->id));
			}
		}

		$this->applyOrdering($query);

//		echo "\n\n<pre>=========getDataQuery=================\n";
//		print_r($query->dump());
//		echo "\n==========================</pre>\n\n";

		return $query;
	}

	protected function applyOrdering(&$query)
	{
		$params = Params::get();

		$ordering = $this->config->getData('ordering');

		if ( ! empty($ordering->orders))
		{
			$query->order(implode(',', $ordering->orders))
				->order('items.' . $params->ordering . ' ' . $params->ordering_direction);

			return;
		}

		if (empty($ordering) || ! is_string($ordering))
		{
			$ordering = $this->items->getOrdering();
		}

		if ( ! $ordering)
		{
			$query->order('items.' . $params->ordering . ' ' . $params->ordering_direction);

			return;
		}

		$query->order($ordering)
			->order('items.' . $params->ordering . ' ' . $params->ordering_direction);
	}
}
