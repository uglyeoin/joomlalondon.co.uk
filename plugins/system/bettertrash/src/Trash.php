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


use JFile;
use JFolder;
use Joomla\CMS\Factory as JFactory;
use RegularLabs\Library\DB as RL_DB;
use RegularLabs\Library\Document as RL_Document;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

/**
 * Plugin that replaces stuff
 */
class Trash
{
	private $params;
	private $db;
	private $data;
	private $component;
	private $storage;

	function __construct($params = null, $db = null, $data = null, $component = null, $storage = null)
	{
		$this->params    = $params ?: Params::get();
		$this->db        = $db ?: JFactory::getDbo();
		$this->data      = $data ?: new Data;
		$this->component = $component ?: new Component;
		$this->storage   = $storage ?: new Storage;
	}

	public function remove()
	{
		if ( ! $this->shouldRemove())
		{
			return;
		}

		$this->storage->addMissing();

		$items = $this->getItemsToRemove();

		foreach ($items as $table => $ids)
		{
			$this->removeByTable($table, $ids);
		}

		$this->updateLogFile();
	}

	private function getItemsToRemove($max_age = 0)
	{
		$max_age = $max_age ?: (float) $this->params->delete_after_days ?: 30;
		$max_age = JFactory::getDate('-' . (int) $max_age . ' days')->toSql();

		$query = $this->db->getQuery(true)
			->select('*')
			->from($this->db->quoteName('#__bettertrash'))
			->where($this->db->quoteName('date') . ' < ' . $this->db->quote($max_age));

		$this->db->setQuery($query);

		$items = $this->db->loadObjectList();

		$grouped = [];

		foreach ($items as $item)
		{
			if ( ! isset($grouped[$item->table]))
			{
				$grouped[$item->table] = [];
			}

			$grouped[$item->table][] = $item->id;
		}

		return $grouped;
	}

	private function removeByTable($table, $ids)
	{
		if (empty($ids))
		{
			return false;
		}

		if ( ! RL_DB::tableExists($table))
		{
			return false;
		}

		$component = $this->component->getByTableName($table);

		if ( ! $component->getData())
		{
			return false;
		}

		// Remove items from the component table
		$component->remove($ids);

		// Remove items from the Better Trash storage table
		$this->storage->remove($ids, $table);

		return true;
	}

	private function shouldRemove()
	{
		if ( ! $this->params->auto_delete)
		{
			return false;
		}

		if ( ! RL_Document::isClient('administrator') || JFactory::getUser()->get('guest'))
		{
			return false;
		}

		return $this->passTimeout();
	}

	private function passTimeout()
	{
		$file = $this->getLogFilePath();

		if ( ! JFile::exists($file))
		{
			return true;
		}

		$lastclean = (int) file_get_contents($file);
		$timeout   = $this->getTimeoutTime();

		// Return false if last clean is within interval
		if ($lastclean > $timeout)
		{
			return false;
		}

		return true;
	}

	private function updateLogFile()
	{
		// Write current time to text file
		$file = $this->getLogFilePath();

		JFile::write($file, time());
	}

	private function getTimeoutTime()
	{
		$clean_every_nr_of_days = 1;
		$timeout_seconds        = $clean_every_nr_of_days * 24 * 60 * 60;

		return time() - $timeout_seconds;
	}

	private function getLogFilePath()
	{
		$log_path = str_replace('\\', '/', $this->params->log_path . '/');
		$log_path = JPATH_SITE . '/' . $log_path;
		$log_path = str_replace('//', '/', $log_path);

		if ( ! JFolder::exists($log_path))
		{
			$log_path = JPATH_PLUGINS . '/system/bettertrash/';
		}

		return $log_path . '/bettertrash_lastclean.log';
	}
}
