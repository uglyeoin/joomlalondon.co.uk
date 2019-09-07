<?php
/**
 * @package         Dummy Content
 * @version         6.0.2PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2019 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\DummyContent;

defined('_JEXEC') or die;

use JFile;
use RegularLabs\Library\RegEx as RL_RegEx;

class WordList
{
	static $list        = [];
	static $type        = 'lorem';
	static $is_sentence = false;

	public static function setType($type)
	{
		self::$is_sentence = false;
		self::$type        = trim(RL_RegEx::replace('[^a-z0-9]', '', strtolower($type)));
		if (substr(self::$type, -5) == 'ipsum')
		{
			self::$type = substr(self::$type, 0, -5);
		}

		switch (self::$type)
		{
			case 'bowie':
				self::$is_sentence = true;
				break;

			case 'business':
				self::$type = 'corporate';
				break;

			case 'fish':
				self::$type = 'fishier';
				break;

			case 'gangster':
				self::$type = 'gangsta';
				break;

			case 'space':
				self::$is_sentence = true;
				break;

			case 'web2':
				self::$type = 'web20';
				break;

			case 'what':
				self::$type = 'whatnothing';
				break;

			case 'arab':
				self::$type = 'arabic';
				break;

			case 'leet':
			case 'l33t':
			case 'l33tspeak':
				self::$type = 'leetspeak';
				break;

			case 'luxembourg':
			case 'letzebuerg':
			case 'letzebuergesch':
				self::$type = 'luxembourgish';
				break;

			case 'volapuk':
				self::$type = 'volapuek';
				break;
		}

		$path = __DIR__ . '/wordlists/';
		if ( ! JFile::exists($path . self::$type . '.txt'))
		{
			self::$type = 'lorem';
		}

	}

	public static function getList()
	{
		if (isset(self::$list[self::$type]))
		{
			return self::$list[self::$type];
		}

		$path  = __DIR__ . '/wordlists/';
		$words = file_get_contents($path . self::$type . '.txt');
		$words = trim(RL_RegEx::replace('(^|\n)\/\/ [^\n]*', '', $words));

		self::$list[self::$type] = explode("\n", $words);

		return self::$list[self::$type];
	}

	public static function isSentenceList()
	{
		return self::$is_sentence;
	}
}
