<?php
/**
 * @package         Modals
 * @version         11.5.2PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2019 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\Modals;

defined('_JEXEC') or die;



class ImageData
{
	static $data_files = [];

	public static function get($folder)
	{
		if (isset(self::$data_files[$folder]))
		{
			return self::$data_files[$folder];
		}

		if ( ! file_exists(JPATH_SITE . '/' . $folder . '/data.txt'))
		{
			return [];
		}

		$data = file_get_contents(JPATH_SITE . '/' . $folder . '/data.txt');

		$data = str_replace("\r", '', $data);
		$data = explode("\n", $data);

		$array = [];
		foreach ($data as $data_line)
		{
			if (empty($data_line)
				|| $data_line[0] == '#'
				|| strpos($data_line, '=') === false
			)
			{
				continue;
			}
			list($key, $val) = explode('=', $data_line, 2);
			$array[$key] = $val;
		}

		self::$data_files[$folder] = $array;

		return self::$data_files[$folder];
	}
}
