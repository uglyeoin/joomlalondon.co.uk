<?php
/**
 * @package         Advanced Module Manager
 * @version         7.11.0PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2019 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\AdvancedModules;

defined('_JEXEC') or die;

use RegularLabs\Library\Parameters as RL_Parameters;
use RegularLabs\Library\PluginTag as RL_PluginTag;
use RegularLabs\Library\RegEx as RL_RegEx;

class Params
{
	protected static $params = null;

	public static function get()
	{
		if ( ! is_null(self::$params))
		{
			return self::$params;
		}

		$params = RL_Parameters::getInstance()->getComponentParams('advancedmodules');

		self::$params = $params;

		return self::$params;
	}

	public static function getRegex($get_surrounding = false)
	{
		$params = self::get();

		// Tag character start and end
		list($tag_start, $tag_end) = self::getTagCharacters();
		$tag_start = RL_RegEx::quote($tag_start);
		$tag_end   = RL_RegEx::quote($tag_end);

		$tags  = RL_RegEx::quote([$params->tag_remove_module, $params->tag_remove_modulepos], 'type');
		$regex = $tag_start
			. $tags
			. ' (?<id>.*?)'
			. $tag_end;

		if ( ! $get_surrounding)
		{
			return $regex;
		}

		$pre  = RL_PluginTag::getRegexSurroundingTagsPre();
		$post = RL_PluginTag::getRegexSurroundingTagsPost();

		return $pre . $regex . $post;
	}

	public static function getTagCharacters()
	{
		$params = self::get();

		if ( ! isset($params->tag_character_start))
		{
			self::setTagCharacters();
		}

		return [$params->tag_character_start, $params->tag_character_end];
	}

	public static function setTagCharacters()
	{
		$params = self::get();

		list(self::$params->tag_character_start, self::$params->tag_character_end) = explode('.', $params->tag_characters);
	}
}
