<?php
/**
 * @package         Cache Cleaner
 * @version         7.1.0PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2019 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;


use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel as JModel;

require_once JPATH_ADMINISTRATOR . '/components/com_jotcache/models/main.php';

class JotCacheMainModelMain extends MainModelMain
{
	public function __construct()
	{
		JModel::__construct();
		$this->app = JFactory::getApplication();
		$pars      = self::getPluginParams();

		if ( ! is_object($pars->storage))
		{
			$pars->storage       = (object) [];
			$pars->storage->type = 'file';
		}

		switch ($pars->storage->type)
		{
			case 'memcache':
				JLoader::register('JotcacheMemcache', JPATH_ADMINISTRATOR . '/components/com_jotcache/helpers/memcache.php');
				$this->storage = new JotcacheMemcache($pars);
				break;
			case 'memcached':
				JLoader::register('JotcacheMemcached', JPATH_ADMINISTRATOR . '/components/com_jotcache/helpers/memcached.php');
				$this->storage = new JotcacheMemcached($pars);
				break;
			default:
				break;
		}

		$this->refresh = new JotcacheRefresh($this->_db, $this->storage);
		$this->store   = new JotcacheStore($this->_db, $this->storage);
	}

	function cleanGlobal($all = false)
	{
		return;
	}
}
