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

namespace RegularLabs\Plugin\System\CacheCleaner\Cache;

defined('_JEXEC') or die;


/**
 * Based on:
 * Siteground Joomla Cache Plugin (jSGCache)
 *
 * @author       George Penkov
 * @category     Siteground Joomla Plugins
 * @package      Siteground Joomla Cache Plugin
 */
class SiteGround extends Cache
{
	public static function purge()
	{
		$purgeRequest = str_replace(
				['administrator/index.php', 'index.php'],
				'',
				str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME'])
			) . '(.*)';

		$sgcache_ip = '/etc/sgcache_ip';

		$hostname     = $_SERVER['SERVER_ADDR'];
		$purge_method = "PURGE";

		// Check if caching server is varnish
		if (file_exists($sgcache_ip))
		{
			if ( ! $hostname = trim(file_get_contents($sgcache_ip, true)))
			{
				self::addError('SG Cache: Connection to cache server failed!');

				return;
			}

			$purge_method = "BAN";
		}

		if ( ! $cacheServerSocket = @fsockopen($hostname, 80, $errno, $errstr, 2))
		{
			self::addError('SG Cache: Connection to cache server failed!');

			return;
		}

		$request = "$purge_method {$purgeRequest} HTTP/1.0\r\nHost: {$_SERVER['SERVER_NAME']}\r\nConnection: Close\r\n\r\n";

		if (preg_match('/^www\./', $_SERVER['SERVER_NAME']))
		{
			$domain_no_www = preg_replace('/^www\./', '', $_SERVER['SERVER_NAME']);
			$request2      = "BAN {$purgeRequest} HTTP/1.0\r\nHost: {$domain_no_www}\r\nConnection: Close\r\n\r\n";
		}
		else
		{
			$request2 = "BAN {$purgeRequest} HTTP/1.0\r\nHost: www.{$_SERVER['SERVER_NAME']}\r\nConnection: Close\r\n\r\n";
		}

		fwrite($cacheServerSocket, $request);
		$response = fgets($cacheServerSocket);
		fclose($cacheServerSocket);

		$cacheServerSocket = fsockopen($hostname, 80, $errno, $errstr, 2);
		fwrite($cacheServerSocket, $request2);
		fclose($cacheServerSocket);

		if ( ! preg_match('/200/', $response))
		{
			self::addError('SG Cache: Purge was not successful!');
		}
	}
}
