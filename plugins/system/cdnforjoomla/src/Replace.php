<?php
/**
 * @package         CDN for Joomla!
 * @version         6.1.3PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2019 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\CDNforJoomla;

defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri as JUri;
use RegularLabs\Library\Protect as RL_Protect;
use RegularLabs\Library\RegEx as RL_RegEx;

class Replace
{
	static $set = null;

	public static function replace(&$string)
	{
		if (is_array($string))
		{
			self::replaceInList($string);

			return;
		}

		if ( ! is_string($string) || $string == '')
		{
			return;
		}

		$sets = Params::getSets();

		if (empty($sets))
		{
			return;
		}

		Protect::_($string);

		foreach ($sets as $set)
		{
			self::replaceBySet($string, $set);
		}

		RL_Protect::unprotect($string);
	}

	private static function replaceInList(&$array)
	{
		foreach ($array as &$val)
		{
			self::replace($val);
		}
	}

	private static function replaceBySet(&$string, $set)
	{
		self::$set = $set;

		self::replaceBySearchList($string, self::$set->searches);

		if ( ! empty(self::$set->enable_in_scripts) && strpos($string, '<script') !== false)
		{
			self::replaceInJavascript($string);
		}
	}

	private static function replaceInJavascript(&$string)
	{
		$regex = '<script(?:\s+(language|type)\s*=[^>]*)?>.*?</script>';

		RL_RegEx::matchAll($regex, $string, $parts);

		if (empty($parts))
		{
			return;
		}

		foreach ($parts as $part)
		{
			self::replaceInJavascriptStringPart($string, $part);
		}
	}

	private static function replaceInJavascriptStringPart(&$string, $part)
	{
		$new_string = $part[0];

		if ( ! self::replaceBySearchList($new_string, self::$set->js_searches))
		{
			return;
		}

		$string = str_replace($part[0], $new_string, $string);
	}

	private static function replaceBySearchList(&$string, $searches)
	{
		$changed = 0;

		foreach ($searches as $word => $search)
		{
			if ( ! is_numeric($word) && strpos($string, $word) == false)
			{
				continue;
			}

			$changed = self::replaceBySearch($string, $search);
		}

		return $changed;
	}

	private static function replaceBySearch(&$string, $search)
	{
		RL_RegEx::matchAll($search, $string, $matches);

		if (empty($matches))
		{
			return false;
		}

		$changed = false;

		foreach ($matches as $match)
		{
			if ( ! self::replaceBySearchMatch($string, $match))
			{
				continue;
			}

			$changed = true;
		}

		return $changed;
	}

	private static function replaceBySearchMatch(&$string, $match)
	{
		if (strpos($match[1], 'srcset') !== false)
		{
			return self::replaceBySearchSrcset($string, $match);
		}

		$file = self::getNewFilePath($match[3]);

		if ( ! $file)
		{
			return false;
		}

		$string = str_replace(
			$match[0],
			$match[1] . $file . $match[4],
			$string
		);

		return true;
	}

	private static function replaceBySearchSrcset(&$string, $match)
	{
		$new_files = $match[3];
		$files     = explode(',', $new_files);

		foreach ($files as $file)
		{
			$new_file = self::getNewFilePath($file);

			if ( ! $new_file)
			{
				continue;
			}

			$new_files = str_replace(
				$file,
				$new_file,
				$new_files
			);
		}

		if ($match[3] == $new_files)
		{
			return false;
		}

		$string = str_replace(
			$match[0],
			$match[1] . $new_files . $match[4],
			$string
		);

		return true;
	}

	private static function getNewFilePath($file)
	{
		$file = RL_RegEx::replace('^' . RL_RegEx::quote(JUri::root()), '', trim($file));

		list($file, $query) = self::getFileParts($file);

		if ( ! $file || self::fileIsIgnored($file))
		{
			return false;
		}

		if (self::$set->enable_versioning
			&& self::includeVersioningFile($file)
			&& file_exists(JPATH_SITE . '/' . $file)
		)
		{
			$query[] = filemtime(JPATH_SITE . '/' . $file);
		}

		return self::getCdnUrl($file)
			. '/' . self::addQueryToFile($file, $query);
	}

	private static function includeVersioningFile($file)
	{
		foreach (self::$set->versioning_filetypes as $filetype)
		{
			if (substr($file, -strlen($filetype)) == $filetype)
			{
				return true;
			}
		}

		return false;
	}

	private static function getFileParts($file)
	{
		$file = trim($file);

		if ( ! $file)
		{
			return [null, null];
		}

		if (strpos($file, '?') === false)
		{
			return [$file, null];
		}

		list($file, $query) = explode('?', $file, 2);
		$query = explode('&', $query);

		return [$file, $query];
	}

	private static function addQueryToFile($file, $query = [])
	{
		$file = trim($file);

		if (empty($query))
		{
			return $file;
		}

		return $file . '?' . implode('&', $query);
	}

	private static function fileIsIgnored($file)
	{
		foreach (self::$set->ignorefiles as $ignore)
		{
			if ($ignore && (strpos($file, $ignore) !== false || strpos(htmlentities($file), $ignore) !== false))
			{
				return true;
			}
		}

		return false;
	}

	private static function getCdnUrl($file)
	{
		$cdns = self::$set->cdns;

		if (count($cdns) > 1)
		{
			// Make sure a file is always served from the same cdn server to leverage browser caching
			$cdns = [$cdns[hexdec(substr(hash('md2', $file), -4)) % count($cdns)]];
		}

		return self::$set->protocol . $cdns[0];
	}
}
