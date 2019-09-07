<?php
/**
 * @package         Better Trash
 * @version         1.3.0PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2019 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\BetterTrash;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

/**
 * Plugin that replaces stuff
 */
class Component
{
	private $_data;

	private $params;
	private $db;
	private $data;
	private $storage;

	function __construct($params = null, $db = null, $data = null, $storage = null)
	{
		$this->params  = $params ?: Params::get();
		$this->db      = $db ?: JFactory::getDbo();
		$this->data    = $data ?: new Data;
		$this->storage = $storage ?: new Storage;
	}

	public function get($component = '', $view = '')
	{
		$this->_data = $this->data->get($component, $view);

		return $this;
	}

	public function getByContext($context)
	{
		$this->_data = $this->data->getByContext($context);

		return $this;
	}

	public function getByTableName($table)
	{
		$this->_data = $this->data->getByTableName($table);

		return $this;
	}

	public function getData()
	{
		return $this->_data;
	}

	public function getTable()
	{
		if ( ! isset($this->_data->table))
		{
			return false;
		}

		return $this->_data->table;
	}

	public function remove($ids)
	{
		$query = $this->db->getQuery(true)
			->delete($this->db->quoteName('#__' . $this->_data->table))
			->where($this->db->quoteName($this->_data->id) . ' IN (' . implode(',', $ids) . ')')
			->where($this->db->quoteName($this->_data->state) . ' = ' . $this->db->quote($this->_data->state_trashed));

		$this->db->setQuery($query);

		$this->db->execute();
	}
}
