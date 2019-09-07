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

class Tables extends Cache
{
	public static function purge()
	{
		$params = Params::get();

		if (empty($params->clean_tables_selection))
		{
			return;
		}

		$tables = explode(',', str_replace("\n", ',', $params->clean_tables_selection));

		foreach ($tables as $table)
		{
			self::emptyTable($table);
		}
	}
}
