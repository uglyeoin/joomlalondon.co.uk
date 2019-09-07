<?php
/**
 * @package         Snippets
 * @version         6.5.4PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2019 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\Snippets;

defined('_JEXEC') or die;

use RegularLabs\Library\Html as RL_Html;
use RegularLabs\Library\Parameters as RL_Parameters;
use RegularLabs\Library\PluginTag as RL_PluginTag;
use RegularLabs\Library\RegEx as RL_RegEx;
use RegularLabs\Library\Uri as RL_Uri;

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

		$params = RL_Parameters::getInstance()->getComponentParams('snippets');

		$params->tag = RL_PluginTag::clean($params->tag);

		$url                 = RL_Uri::get();
		$params->cookie_name = 'rl_snippets_' . md5($url);

		self::$params = $params;

		return self::$params;
	}

	public static function getTags($only_start_tags = false)
	{
		$params = self::get();

		list($tag_start, $tag_end) = self::getTagCharacters();

		$tags = [
			[
				$tag_start . $params->tag,
			],
			[
				$tag_end,
			],
		];

		if ($params->tag == 'snippet')
		{
			$tags[0][] = $tag_start . $params->tag . 's';
			$tags[1][] = $tag_start . '/' . $params->tag . 's' . $tag_end;
		}

		return $only_start_tags ? $tags[0] : $tags;
	}

	public static function getRegex($type = 'tag')
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

		$params = self::get();

		// Tag character start and end
		list($tag_start, $tag_end) = Params::getTagCharacters();

		$inside_tag     = RL_PluginTag::getRegexInsideTag($tag_start, $tag_end);
		$spaces         = RL_PluginTag::getRegexSpaces();
		$block_elements = RL_Html::getBlockElements(['div']);

		$tag_start = RL_RegEx::quote($tag_start);
		$tag_end   = RL_RegEx::quote($tag_end);

		$pre  = '(<(?<pretag>' . implode('|', $block_elements) . ')(?: [^>]*)?>)?';
		$post = '(</(?<posttag>' . implode('|', $block_elements) . ')>)?';

		$tag_regex = RL_RegEx::quote($params->tag) . (($params->tag == 'snippet') ? 's?' : '');

		self::$regexes = (object) [];

		self::$regexes->tag =
			'(?<pre>' . $pre . ')'
			. $tag_start . $tag_regex . $spaces . '(?<id>' . $inside_tag . ')' . $tag_end
			. '(?<post>' . $post . ')';

		return self::$regexes;
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
