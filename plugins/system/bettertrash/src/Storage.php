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
use RegularLabs\Library\DB as RL_DB;

/**
 * Plugin that replaces stuff
 */
class Storage
{
	private $params  = null;
	private $db      = null;
	private $context = null;
	private $data    = null;

	function __construct($params = null, $db = null, $data = null)
	{
		$this->params = $params ?: Params::get();
		$this->db     = $db ?: JFactory::getDbo();
		$this->data   = $data ?: new Data;
	}

	public function setContext($context = null)
	{
		$this->context = $context ?: $this->context;

		return $this;
	}

	public function updateList($ids, $state, $context)
	{
		$this->setContext($context);

		if ( ! $this->getData())
		{
			return;
		}

		foreach ($ids as $id)
		{
			$this->update($id, $state);
		}
	}

	public function updateItem($item, $isNew, $context)
	{
		$this->setContext($context);

		// No item found
		if (empty($item))
		{
			return;
		}

		// No id found
		if ( ! isset($item->id) || ! $item->id)
		{
			return;
		}

		// No matching table data found
		if ( ! $data = $this->getData())
		{
			return;
		}

		// No state found
		if ( ! isset($item->{$data->state}))
		{
			return;
		}

		$state = $item->{$data->state};

		// New item that is not trashed: nothing to do
		if ($isNew && $state != $data->state_trashed)
		{
			return;
		}

		$this->update($item->id, $state);
	}

	public function removeItem($item, $context)
	{
		$this->setContext($context);

		// No item found
		if (empty($item))
		{
			return;
		}

		// No id found
		if ( ! isset($item->id) || ! $item->id)
		{
			return;
		}

		$this->remove($item->id);
	}

	public function update($id, $state, $data = null)
	{
		if ( ! $id)
		{
			return;
		}

		$data = $data ?: $this->getData();

		if ( ! $data)
		{
			return;
		}

		$state = self::getState($state, $data);

		if ($state != $data->state_trashed)
		{
			$this->remove($id);

			return;
		}

		$this->add($id);
	}

	public function remove($ids = 0, $table = '')
	{
		if (empty($ids))
		{
			return;
		}

		if ( ! $table)
		{
			return;
		}

		if ( ! is_array($ids))
		{
			$ids = [$ids];
		}

		$query = $this->db->getQuery(true)
			->delete($this->db->quoteName('#__bettertrash'))
			->where($this->db->quoteName('table') . ' = ' . $this->db->quote($table))
			->where($this->db->quoteName('id') . ' IN (' . implode(',', $ids) . ')');

		$this->db->setQuery($query);

		$this->db->execute();
	}

	private function add($ids = 0, $data = null, $overwrite = true)
	{
		if (empty($ids))
		{
			return;
		}

		$data = $data ?: $this->getData();

		if (empty($data))
		{
			return;
		}

		if ( ! is_array($ids))
		{
			$ids = [$ids];
		}

		foreach ($ids as $key => $id)
		{
			if ( ! $this->has($id, $data))
			{
				continue;
			}

			if ($overwrite)
			{
				$this->remove($id, $data);
				continue;
			}

			unset($ids[$key]);
		}

		if (empty($ids))
		{
			return;
		}

		$values = [];
		$date   = JFactory::getDate()->toSql();

		foreach ($ids as $key => $id)
		{
			$values[] = $this->db->quote($data->table) . ', '
				. $id . ', '
				. $this->db->quote($date);
		}

		$query = $this->db->getQuery(true)
			->insert($this->db->quoteName('#__bettertrash'))
			->columns($this->db->quoteName(['table', 'id', 'date']))
			->values($values);

		$this->db->setQuery($query);

		$this->db->execute();
	}

	private function has($id = 0, $data = null)
	{
		if ( ! $id)
		{
			return false;
		}

		$data = $data ?: $this->getData();

		if (empty($data))
		{
			return false;
		}

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('id'))
			->from($this->db->quoteName('#__bettertrash'))
			->where($this->db->quoteName('table') . ' = ' . $this->db->quote($data->table))
			->where($this->db->quoteName('id') . ' = ' . (int) $id);

		$this->db->setQuery($query);

		return $this->db->loadResult();
	}

	private function getData($context = null)
	{
		$context = $context ?: $this->context;

		return $data = $this->data->getByContext($context);
	}

	private function getState($state, $data)
	{
		if (empty($data->published_to_trashed_state))
		{
			return $state;
		}

		if ( ! isset($data->published_to_trashed_state[$state]))
		{
			return 0;
		}

		return $data->published_to_trashed_state[$state];
	}

	public function addMissing()
	{
		$all_data = $this->data->getAll();

		foreach ($all_data as $data)
		{
			$this->addMissingByData($data);
		}
	}

	private function addMissingByData($data)
	{
		$ids = $this->getTrashedIdsByData($data);

		if (empty($ids))
		{
			return;
		}

		$this->add($ids, $data, false);
	}

	private function getTrashedIdsByData($data)
	{
		if ( ! RL_DB::tableExists($data->table))
		{
			return false;
		}

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName($data->id))
			->from($this->db->quoteName('#__' . $data->table))
			->where($this->db->quoteName($data->state) . ' = ' . $data->state_trashed);

		$this->db->setQuery($query);

		return $this->db->loadColumn();
	}
}
