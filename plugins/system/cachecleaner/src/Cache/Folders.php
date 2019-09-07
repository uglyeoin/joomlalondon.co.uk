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

use RegularLabs\Plugin\System\CacheCleaner\Params;

class Folders extends Cache
{
	// Empty tmp folder
	public static function purge_tmp()
	{
		$min_age = Params::get()->clean_tmp_min_age;
		self::emptyFolder(JPATH_SITE . '/tmp', $min_age);
	}

	// Empty custom folder
	public static function purge_folders()
	{
		$params = Params::get();

		if (empty($params->clean_folders_selection))
		{
			return;
		}

		$min_age = $params->clean_folders_min_age;
		$folders = explode("\n", str_replace('\n', "\n", $params->clean_folders_selection));

		foreach ($folders as $folder)
		{
			if ( ! trim($folder))
			{
				continue;
			}

			$folder = rtrim(str_replace('\\', '/', trim($folder)), '/');
			$path   = str_replace('//', '/', JPATH_SITE . '/' . $folder);

			self::emptyFolder($path, $min_age);
		}
	}
}
