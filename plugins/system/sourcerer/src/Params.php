<?php
/**
 * @package         Sourcerer
 * @version         8.0.0PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2019 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\Sourcerer;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use RegularLabs\Library\Parameters as RL_Parameters;
use RegularLabs\Library\PluginTag as RL_PluginTag;
use RegularLabs\Library\RegEx as RL_RegEx;

class Params
{
	protected static $params  = null;
	protected static $regexes = null;
	protected static $areas   = null;

	public static function get($key = '', $default = '')
	{
		if ($key != '')
		{
			return self::getByKey($key, $default);
		}

		if ( ! is_null(self::$params))
		{
			return self::$params;
		}

		$params = RL_Parameters::getInstance()->getPluginParams('sourcerer');

		$params->tag = RL_PluginTag::clean($params->syntax_word);

		$params->html_tags_syntax = [['<', '>'], ['\[\[', '\]\]']];
		$params->splitter         = '<!-- START: SRC_SPLIT -->';

		$params->include_path  = str_replace('//', '/', ('/' . trim($params->include_path, ' /\\') . '/'));
		$params->user_is_admin = JFactory::getUser()->authorise('core.admin', 1);

		$params->disabled_components = $params->components;
		if ( ! is_array($params->disabled_components))
		{
			$params->disabled_components = explode(',', $params->disabled_components);
		}

		self::$params = $params;

		return self::$params;
	}

	private static function getByKey($key, $default = '')
	{
		$params = self::get();

		return ! empty($params->{$key}) ? $params->{$key} : $default;
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
				$tag_start . '/' . $params->tag . $tag_end,
			],
		];

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
		$tag_start = RL_RegEx::quote($tag_start);
		$tag_end   = RL_RegEx::quote($tag_end);

		$pre  = RL_PluginTag::getRegexSurroundingTagPre();
		$post = RL_PluginTag::getRegexSurroundingTagPost();

		$spaces = RL_PluginTag::getRegexSpaces('*');

		self::$regexes = (object) [];

		self::$regexes->tag = '('
			. '(?<start_pre>' . $pre . ')'
			. $tag_start . RL_RegEx::quote($params->tag) . $spaces . '(?<data>( .*?)?)' . $tag_end
			. '(?<start_post>' . $post . ')'

			. '(?<content>.*?)'

			. '(?<end_pre>' . $pre . ')'
			. $tag_start . '\/' . RL_RegEx::quote($params->tag) . $tag_end
			. '(?<end_post>' . $post . ')'
			. ')';

		return self::$regexes;
	}

	public static function getArea($type = 'default')
	{
		$areas = self::getAreaSettings();

		return isset($areas->{$type}) ? $areas->{$type} : $areas->default;
	}

	public static function getAreaSettings()
	{
		if ( ! is_null(self::$areas))
		{
			return self::$areas;
		}

		$areas = (object) [];

		// Initialise the different enables
		$areas->default = self::getAreaDefault();
		$areas->articles   = self::getAreaByType('articles');
		$areas->components = self::getAreaByType('components');
		$areas->other      = self::getAreaByType('other');

		self::$areas = $areas;

		return self::$areas;
	}

	private static function getAreaDefault()
	{
		$params = self::get();

		return (object) [
			'enable'         => true,
			'enable_css'     => $params->enable_css,
			'enable_js'      => $params->enable_js,
			'enable_php'     => $params->enable_php,
			'forbidden_php'  => $params->forbidden_php,
			'forbidden_tags' => $params->forbidden_tags,
		];
	}

	private static function getAreaByType($type = 'default')
	{
		$params = self::get();

		$default = self::getAreaDefault();

		$area = (object) [
			'enable'         => $params->{$type . '_enable'},
			'enable_css'     => $params->{$type . '_enable_css'},
			'enable_js'      => $params->{$type . '_enable_js'},
			'enable_php'     => $params->{$type . '_enable_php'},
			'forbidden_php'  => $default->forbidden_php . ',' . $params->{$type . '_forbidden_php'},
			'forbidden_tags' => $default->forbidden_tags . ',' . $params->{$type . '_forbidden_tags'},
		];

		if ($area->enable_css == -1)
		{
			$area->enable_css = $default->enable_css;
		}

		if ($area->enable_js == -1)
		{
			$area->enable_js = $default->enable_js;
		}

		if ($area->enable_php == -1)
		{
			$area->enable_php = $default->enable_php;
		}

		$option = JFactory::getApplication()->input->get('option');

		if ($type == 'components' && in_array($option, $params->disabled_components))
		{
			$area->enable     = false;
			$area->enable_css = false;
			$area->enable_js  = false;
			$area->enable_php = false;
		}

		return $area;
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
