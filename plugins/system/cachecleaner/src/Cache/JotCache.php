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


use Joomla\CMS\Filesystem\File as JFile;
use JotCacheMainModelMain;

class JotCache extends Cache
{
	public static function purge()
	{
		$file = JPATH_ADMINISTRATOR . '/components/com_jotcache/models/main.php';

		if ( ! JFile::exists($file))
		{
			return;
		}

		require_once __DIR__ . '/JotCacheMainModelMain.php';

		$model = new JotCacheMainModelMain;
		$model->deleteall();
	}
}
