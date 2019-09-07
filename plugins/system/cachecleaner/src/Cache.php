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

namespace RegularLabs\Plugin\System\CacheCleaner;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Filesystem\File as JFile;
use Joomla\CMS\Filesystem\Folder as JFolder;
use Joomla\CMS\Http\HttpFactory as JHttpFactory;
use Joomla\CMS\Language\Text as JText;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Language as RL_Language;

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

class Cache
{
	static $show_message = true;
	static $message      = '';
	static $error        = '';
	static $thirdparties = ['jotcache', 'siteground', 'maxcdn', 'keycdn', 'cdn77', 'cloudflare'];

	public static function clean()
	{
		self::purgeThirdPartyCacheByUrl();

		if ( ! self::getCleanType())
		{
			return false;
		}

		// Run the main purge actions
		$result = self::purge();

		// only handle messages in html
		if ( ! RL_Document::isHtml())
		{
			return false;
		}

		if (JFactory::getApplication()->input->getInt('break'))
		{
			die(
			str_replace('<br>',
				' - ',
				$result->error ?: '+' . $result->message
			)
			);
		}

		if (self::$show_message && $result->message)
		{
			JFactory::getApplication()->enqueueMessage(
				$result->error ?: $result->message,
				($result->error ? 'error' : 'message')
			);
		}

		return true;
	}

	private static function getCleanType()
	{
		$params = Params::get();

		$cleancache = trim(JFactory::getApplication()->input->getString('cleancache'));

		// Clean via url
		if ( ! empty($cleancache))
		{
			// Return if on frontend and no secret url key is given
			if (RL_Document::isClient('site') && $cleancache != $params->frontend_secret)
			{
				return '';
			}

			// Return if on login page
			if (RL_Document::isClient('administrator') && JFactory::getUser()->get('guest'))
			{
				return '';
			}

			if (JFactory::getApplication()->input->getWord('src') == 'button')
			{
				return 'button';
			}

			self::$show_message = true;

			if (RL_Document::isClient('site') && $cleancache == $params->frontend_secret)
			{
				self::$show_message = $params->frontend_secret_msg;
			}

			return 'clean';
		}

		// Clean via save task
		if (self::passTask())
		{
			return 'save';
		}

		// Clean via interval
		if (self::passInterval())
		{
			return 'interval';
		}

		return '';
	}

	private static function passTask()
	{
		$params = Params::get();

		if ( ! $task = JFactory::getApplication()->input->get('task'))
		{
			return false;
		}

		$task = explode('.', $task, 2);
		$task = isset($task[1]) ? $task[1] : $task[0];
		if (strpos($task, 'save') === 0)
		{
			$task = 'save';
		}

		$tasks = array_diff(array_map('trim', explode(',', $params->auto_save_tasks)), ['']);

		if (empty($tasks) || ! in_array($task, $tasks))
		{
			return false;
		}

		if (RL_Document::isClient('administrator') && $params->auto_save_admin)
		{
			self::$show_message = $params->auto_save_admin_msg;

			return true;
		}

		if (RL_Document::isClient('site') && $params->auto_save_front)
		{
			self::$show_message = $params->auto_save_front_msg;

			return true;
		}

		return false;
	}

	public static function purge()
	{
		$params = Params::get();

		// Joomla cache
		if (self::passType('purge'))
		{
			Cache\Joomla::purge();
		}

		// 3rd party cache
		self::purgeThirdPartyCaches();

		// Folders
		if (self::passType('clean_tmp'))
		{
			Cache\Folders::purge_tmp();
		}
		if (self::passType('clean_folders'))
		{
			Cache\Folders::purge_folders();
		}

		// Tables
		if (self::passType('clean_tables'))
		{
			Cache\Tables::purge();
		}

		// Purge LiteSpeed cache
		if (self::passType('purge_litespeed'))
		{
			Cache\Joomla::purgeLiteSpeed();
		}

		// Purge OPcache
		if (self::passType('purge_opcache'))
		{
			Cache\Joomla::purgeOPcache();
		}

		// Purge expired cache
		if (self::passType('purge'))
		{
			Cache\Joomla::purgeExpired();
		}

		// Purge update cache
		if (self::passType('purge_updates'))
		{
			Cache\Joomla::purgeUpdates();
		}

		// Global check-in
		if (self::passType('checkin'))
		{
			Cache\Joomla::checkIn();
		}

		if (self::passType('query_url') && ! empty($params->query_url_selection))
		{
			self::queryUrl();
		}

		self::updateLog();

		return self::getResult();
	}

	private static function passType($type)
	{
		$params = Params::get();

		if (empty($params->{$type}))
		{
			return false;
		}

		if ($params->{$type} == 2 && self::getCleanType() != 'button')
		{
			return false;
		}

		return true;
	}

	private static function purgeThirdPartyCaches()
	{
		foreach (self::$thirdparties as $thirdparty)
		{
			if ( ! self::passType('clean_' . $thirdparty))
			{
				continue;
			}

			self::purgeThirdPartyCache($thirdparty);
		}
	}

	private static function purgeThirdPartyCache($thirdparty)
	{
		switch ($thirdparty)
		{
			case 'jotcache':
				return Cache\JotCache::purge();

			case 'siteground':
				return Cache\SiteGround::purge();

			case 'maxcdn':
				return Cache\MaxCDN::purge();

			case 'keycdn':
				return Cache\KeyCDN::purge();

			case 'cdn77':
				return Cache\CDN77::purge();

			case 'cloudflare':
				return Cache\CloudFlare::purge();
		}

		return '';
	}

	private static function purgeThirdPartyCacheByUrl()
	{
		$purged = false;

		foreach (self::$thirdparties as $thirdparty)
		{
			if ( ! JFactory::getApplication()->input->getInt('purge_' . $thirdparty))
			{
				continue;
			}

			if (self::purgeThirdPartyCache($thirdparty) < 0)
			{
				self::addError('(<em>' . JText::_('CC_SAVE_SETTINGS_FIRST') . '</em>)');
			}

			$purged = true;
		}

		if ($purged)
		{
			echo self::getError() ?: '+' . Cache\Cache::getMessage();
			die;
		}
	}

	private static function queryUrl()
	{
		$params = Params::get();

		try
		{
			$http = JHttpFactory::getHttp()->get($params->query_url_selection, null, max(1, $params->query_url_timeout));
			if ($http->code != 200)
			{
				self::setError(JText::sprintf('CC_ERROR_QUERY_URL_FAILED', $params->query_url_selection));
			}
		}
		catch (RuntimeException $e)
		{
			self::setError(JText::sprintf('CC_ERROR_QUERY_URL_FAILED', $params->query_url_selection));
		}
	}

	private static function passInterval()
	{
		$params = Params::get();

		if (
			(RL_Document::isClient('administrator') && ! $params->auto_interval_admin)
			|| (RL_Document::isClient('site') && ! $params->auto_interval_front)
		)
		{
			return false;
		}

		if (RL_Document::isClient('administrator') && JFactory::getUser()->get('guest'))
		{
			return false;
		}

		$file_path = str_replace('//', '/', JPATH_SITE . '/' . str_replace('\\', '/', $params->log_path . '/'));
		if ( ! JFolder::exists($file_path))
		{
			$file_path = JPATH_PLUGINS . '/system/cachecleaner/';
		}
		$file = $file_path . '/cachecleaner_lastclean.log';

		$secs = RL_Document::isClient('site') ? $params->auto_interval_front_secs : $params->auto_interval_admin_secs;

		// Return false if last clean is within interval
		if (
			JFile::exists($file)
			&& ($lastclean = JFile::read($file))
			&& $lastclean > (time() - $secs)
		)
		{
			return false;
		}

		self::$show_message = RL_Document::isClient('site') ? $params->auto_interval_front_msg : $params->auto_interval_admin_msg;

		return true;
	}

	private static function updateLog()
	{
		$params = Params::get();

		// Write current time to text file

		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		$file_path = str_replace('//', '/', JPATH_SITE . '/' . str_replace('\\', '/', $params->log_path . '/'));

		if ( ! JFolder::exists($file_path))
		{
			$file_path = JPATH_PLUGINS . '/system/cachecleaner/';
		}

		$time = time();
		JFile::write($file_path . 'cachecleaner_lastclean.log', $time);
	}

	public static function getResult($show_size = null)
	{
		$show_size = ! is_null($show_size) ? $show_size : Params::get()->show_size;

		// Load language for messaging
		RL_Language::load('mod_cachecleaner');

		$result = (object) [
			'error'   => self::getError(),
			'message' => self::$message ?: JText::_('CC_CACHE_CLEANED'),
		];

		if ($result->error)
		{
			$error = JText::_('CC_NOT_ALL_CACHE_COULD_BE_REMOVED');
			$error .= $result->error !== true ? '<br>' . $result->error : '';

			$result->error = $error;

			return $result;
		}

		if ($show_size && Cache\Cache::getSize())
		{
			$result->message .= ' (' . Cache\Cache::getSize() . ')';
		}

		return $result;
	}

	public static function getMessage()
	{
		return self::$message;
	}

	public static function getError()
	{
		return self::$error;
	}

	public static function setMessage($message = '')
	{
		self::$message = $message;
	}

	public static function setError($error = '')
	{
		self::$error = $error;
	}

	public static function addMessage($message = '')
	{
		self::$message .= self::$message ? '<br>' : '';
		self::$message .= $message;
	}

	public static function addError($error = '')
	{
		self::$error .= self::$error ? '<br>' : '';
		self::$error .= $error;
	}
}
