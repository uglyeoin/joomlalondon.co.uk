<?php
/**
 * @package         Email Protector
 * @version         4.3.2
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2019 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\EmailProtector;

defined('_JEXEC') or die;

use RegularLabs\Library\Parameters as RL_Parameters;

class Params
{
	protected static $params  = null;
	protected static $regexes = null;

	public static function get()
	{
		if ( ! is_null(self::$params))
		{
			return self::$params;
		}

		$params = RL_Parameters::getInstance()->getPluginParams('emailprotector');

		$params->id_pre  = substr(md5('a' . rand(1000, 9999)), 0, 4);
		$params->id_post = substr(md5('b' . rand(1000, 9999)), 0, 4);

		self::$params = $params;

		return self::$params;
	}

	public static function getRegex($type = 'email')
	{
		$regexes = self::getRegexes();

		return isset($regexes->{$type}) ? $regexes->{$type} : $regexes->tag;
	}

	private static function getRegexes()
	{
		if ( ! is_null(self::$regexes))
		{
			return self::$regexes;
		}

		self::$regexes = (object) [];

		// email@domain.com
		$email = '([\w\.\-\+]+\@\w[\w\.\-]*\.\w{2,20})';

		self::$regexes->email  = $email;
		self::$regexes->simple = '[\w\.\-\+]\@\w';
		self::$regexes->js     = '<script[^>]*[^/]>.*?</script>';
		self::$regexes->injs   = '([\'"])' . $email . '\1';
		self::$regexes->link   = '<a\s+((?:[^>]*\s+)?)href\s*=\s*"mailto:(' . $email . '(?:\?[^"]+)?)"((?:\s+[^>]*)?)>(.*?)</a>';

		return self::$regexes;
	}
}
