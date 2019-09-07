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

namespace RegularLabs\Plugin\System\ArticlesAnywhere\PluginTags;

defined('_JEXEC') or die;

use JDatabaseDriver;
use Joomla\CMS\Factory as JFactory;
use RegularLabs\Library\ArrayHelper as RL_Array;
use RegularLabs\Library\RegEx as RL_RegEx;
use RegularLabs\Plugin\System\ArticlesAnywhere\Collection\Fields\CustomFields;
use RegularLabs\Plugin\System\ArticlesAnywhere\Config;
use RegularLabs\Plugin\System\ArticlesAnywhere\Params;

class Ordering
{
	/* @var Config */
	protected $config;

	/* @var JDatabaseDriver */
	private $db;

	public function __construct(Config $config, CustomFields $custom_fields)
	{
		$this->config        = $config;
		$this->db            = JFactory::getDbo();
		$this->custom_fields = $custom_fields->getAvailableFields();
	}

	public function get($attributes)
	{
		$params = Params::get();

		$ordering           = isset($attributes->ordering) ? $attributes->ordering : $params->ordering;
		$ordering_direction = isset($attributes->ordering_direction) ? $attributes->ordering_direction : $params->ordering_direction;

		if (strpos($ordering, ' ') === false)
		{
			$ordering = $ordering . ' ' . $ordering_direction;
		}

		$orderings = RL_Array::toArray($ordering, ',');

		return $this->getOrderings($orderings, $ordering_direction);

	}

	protected function getOrderings($orderings, $default_direction = 'ASC')
	{
		$params = Params::get();

		$orders = [];
		$joins  = [];

		foreach ($orderings as $ordering)
		{
			$ordering_direction = $default_direction;

			// It's an input value [input:id], [input:name:default], etc
			if (RL_RegEx::match('^input:([^"]+)$', $ordering, $match))
			{
				list($value, $default) = explode(':', $match[1] . ':' . $params->ordering);

				$ordering = JFactory::getApplication()->input->getString($value, $default);
			}

			if (strpos($ordering, ' ') !== false)
			{
				list($ordering, $ordering_direction) = explode(' ', $ordering, 2);
			}

			$continue = $this->parse($ordering, $joins, $ordering_direction);

			if ($ordering)
			{
				$orders[] = $ordering;
			}

			if ( ! $continue)
			{
				break;
			}
		}

		return (object) compact('orders', 'joins');
	}

	protected function parse(&$ordering, &$joins, $ordering_direction = 'ASC')
	{
		switch ($ordering)
		{
			case 'none':
				$ordering = '';

				return false;

			case 'featured-ordering':
				$joins[]  = 'frontpage';
				$ordering = $this->db->quoteName('frontpage.ordering');

				return false;

			case 'random':
				$ordering = 'RAND()';

				return false;

			case 'category':
			case 'category-title':
				$joins[]  = 'categories';
				$ordering = $this->db->quoteName('categories.' . $this->config->get('categories_title', false));
				break;

			case 'category-alias':
				$joins[]  = 'categories';
				$ordering = $this->db->quoteName('categories.' . $this->config->get('categories_alias', false));
				break;

			case 'category-id':
				$joins[]  = 'categories';
				$ordering = $this->db->quoteName('categories.id');
				break;

			case 'category-order':
			case 'category-ordering':
				$joins[]  = 'categories';
				$ordering = $this->db->quoteName('categories.lft');
				break;

			case 'author':
			case 'author-name':
				$joins[]  = 'users';
				$ordering = $this->db->quoteName('user.name');
				break;

			case 'author-id':
				$joins[]  = 'users';
				$ordering = $this->db->quoteName('user.id');
				break;

			case 'modifier':
			case 'modifier-name':
				$joins[]  = 'modifiers';
				$ordering = $this->db->quoteName('modifier.name');
				break;

			case 'modifier-id':
				$joins[]  = 'modifiers';
				$ordering = $this->db->quoteName('modifier.id');
				break;

			default:
				$columns = $this->getColumns();

				if ($field = CustomFields::getByName($this->custom_fields, $ordering))
				{
					$joins[] = $ordering;

					$db_field   = $this->db->quoteName('custom_field_' . $ordering);
					$is_numeric = '(' . $db_field . ' REGEXP (\'^-?[0-9\.]+$\'))';

					$ordering = $db_field . ' IS NULL,'
						. 'CASE  ' . $is_numeric . ' WHEN 1 THEN ' . $db_field . ' + 0 END ' . $ordering_direction . ','
						. 'CASE ' . $is_numeric . ' WHEN 0 THEN ' . $db_field . ' END ' . $ordering_direction;

					return true;
				}

				if ( ! in_array($ordering, $columns))
				{
					$ordering = '';

					return true;
				}

				$ordering = $this->db->quoteName('items.' . $ordering);
				break;
		}

		$ordering = $ordering . ' IS NULL,'
			. $ordering . ' = ' . $this->db->quote('') . ','
			. $ordering . ' ' . $ordering_direction;

		return true;
	}

	protected function getColumns()
	{
		$columns = $this->db->getTableColumns($this->config->getTableItems(false));

		return array_keys($columns);
	}
}
