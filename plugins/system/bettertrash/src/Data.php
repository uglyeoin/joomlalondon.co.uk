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

use Doctrine\Common\Inflector\Inflector as BT_Inflector;
use JFolder;
use Joomla\CMS\Factory as JFactory;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

/**
 * Plugin that replaces stuff
 */
class Data
{
	private $data = [];

	public function get($component = '', $view = '')
	{
		$component = $component ?: JFactory::getApplication()->input->get('option');
		$view      = $view ?: JFactory::getApplication()->input->get('view');

		if (strpos($component, 'com_') === 0)
		{
			$component = substr($component, 4);
		}

		$context = $component . '.' . $view;

		if (isset($this->data[$context]))
		{
			return $this->data[$context];
		}

		if (empty($view))
		{
			$view = 0;
		}

		$this->data[$context] = false;

		$file = __DIR__ . '/components/' . $component . '.json';

		$data = $this->getByFile($file, $view);

		$this->data[$context]                 = $data;
		$this->data[$component . '.' . $view] = $data;

		return $this->data[$context];
	}

	private function getByFile($file, $view = '')
	{
		if ( ! is_file($file))
		{
			return false;
		}

		$data = json_decode(file_get_contents($file), true);

		return $this->getByFileData($data, $view);
	}

	private function getByFileData($data, $view = '')
	{
		if (empty($data))
		{
			return false;
		}

		// View is empty, return array of all views
		if ($view === '' && count($data) > 1)
		{
			$all = [];

			foreach ($data as $key => $item)
			{
				$all[] = $this->getByFileData($data, $key);
			}

			return $all;
		}

		// View is set to 0, grab first view
		// View is empty, but data only contains one view, grab first view
		if ($view === 0 || $view === '')
		{
			$view = key($data);
		}

		if ( ! isset($data[$view]))
		{
			return false;
		}

		$data = $data[$view];

		$default_data = [
			'table'          => '',
			'id'             => 'id',
			'state'          => 'state',
			'state_trashed'  => -2,
			'filter'         => 'published',
			'filter_prefix'  => 'filter_',
			'filter_trashed' => -2,
			'action_trash'   => 'trash',
			'action_delete'  => 'delete',
		];

		return (object) array_merge($default_data, $data);
	}

	public function getByContext($context)
	{
		list($component, $view) = explode('.', $context);

		if (empty($view))
		{
			return false;
		}

		$view = BT_Inflector::pluralize($view);

		return $this->get($component, $view);
	}

	public function getByTableName($table)
	{
		$all_data = $this->getAll();

		foreach ($all_data as $data)
		{
			if ($data->table != $table)
			{
				continue;
			}

			return $data;
		}

		return false;
	}

	public function getAll()
	{
		$folder = __DIR__ . '/components';
		$files  = JFolder::files($folder, '\.json$');

		$all_data = [];

		foreach ($files as $file)
		{
			$data = $this->getByFile($folder . '/' . $file);

			if (is_array($data))
			{
				$all_data = array_merge($all_data, $data);
				continue;
			}

			$all_data[] = $data;
		}

		return $all_data;
	}
}
