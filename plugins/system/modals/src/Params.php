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

use RegularLabs\Library\ArrayHelper as RL_Array;
use RegularLabs\Library\Parameters as RL_Parameters;
use RegularLabs\Library\PluginTag as RL_PluginTag;
use RegularLabs\Library\RegEx as RL_RegEx;

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

		$params = RL_Parameters::getInstance()->getPluginParams('modals');

		$params->tag = RL_PluginTag::clean($params->tag);
		$params->tag_content = RL_PluginTag::clean($params->tag_content);

		$params->class = 'modal_link';
		// array_filter will remove any empty values
		$params->classnames = $params->autoconvert_classnames ? RL_Array::toArray(str_replace(' ', ',', trim($params->classnames))) : [];
		$params->classnames_images = $params->autoconvert_classnames_images ? RL_Array::toArray(str_replace(' ', ',', trim($params->classnames_images))) : [];
		$params->filetypes         = $params->autoconvert_filetypes ? RL_Array::toArray(str_replace([' ', '.'], '', $params->filetypes)) : [];
		$params->urls              = $params->autoconvert_urls ? RL_Array::toArray(str_replace("\r", '', $params->urls), "\n") : [];
		$params->auto_group_id     = uniqid('gallery_');

		$params->mediafiles  = RL_Array::toArray(strtolower($params->mediafiles));
		$params->iframefiles = RL_Array::toArray(strtolower($params->iframefiles));

		$params->paramNamesCamelcase = [
			'innerWidth', 'innerHeight', 'initialWidth', 'initialHeight',
			'maxWidth', 'maxHeight', 'minWidth', 'minHeight', 'className',
			'scalePhotos',
			'retinaImage', 'retinaUrl', 'retinaSuffix',
			'returnFocus', 'fastIframe',
			'closeButton', 'overlayClose', 'escKey', 'arrowKey', 'xhrError', 'imgError',
			'slideshowSpeed', 'slideshowAuto', 'slideshowStart', 'slideshowStop',
			'onOpen', 'onLoad', 'onComplete', 'onCleanup', 'onClosed',
		];
		$params->paramNamesLowercase = array_map('strtolower', $params->paramNamesCamelcase);
		$params->paramNamesBooleans  = [
			'scalephotos', 'scrolling', 'inline', 'iframe', 'fastiframe',
			'photo', 'preloading', 'retinaimage', 'open', 'returnfocus', 'trapfocus', 'reposition',
			'loop', 'slideshow', 'slideshowauto', 'overlayclose', 'closebutton', 'esckey', 'arrowkey', 'fixed',
			'overlay',
		];

		$params->booleans = [
			'openOnce', 'inline', 'iframe', 'fullpage',
			'auto_titles',
			'scalephotos',
			'retinaimage', 'retinaurl',
			'overlay',
			'returnfocus', 'fastiframe',
			'overlayclose', 'closebutton', 'countdown', 'esckey', 'arrowkey',
			'fixed', 'reposition',
			'loop', 'preloading', 'slideshow', 'slideshowauto',
			'navigation',
			'gallery_showall', 'auto_group',
			'autoplay',
		];

		self::$params = $params;

		return self::$params;
	}

	public static function getSettings()
	{
		$params = self::get();

		$settings = [];

		foreach ($params as $key => $value)
		{
			$key = str_replace('_', '-', $key);

			$settings[$key] = $value;
		}

		return (object) $settings;
	}

	public static function getTags($only_start_tags = false)
	{
		$params = self::get();

		list($tag_start, $tag_end) = self::getTagCharacters();

		$tags = [
			[
				$tag_start . $params->tag,
				$tag_start . $params->tag_content,
			],
			[
				$tag_start . '/' . $params->tag . $tag_end,
				$tag_start . '/' . $params->tag_content . $tag_end
			],
		];

		return $only_start_tags ? $tags[0] : $tags;
	}

	public static function getTagWords()
	{
		$params = self::get();

		return [
			$params->tag,
			$params->tag_content,
		];
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

		$pre        = RL_PluginTag::getRegexSurroundingTagsPre();
		$post       = RL_PluginTag::getRegexSurroundingTagsPost();
		$inside_tag = RL_PluginTag::getRegexInsideTag($tag_start, $tag_end);

		$tag_start = RL_RegEx::quote($tag_start);
		$tag_end   = RL_RegEx::quote($tag_end);

		$spaces      = RL_PluginTag::getRegexSpaces();
		$spaces_none = RL_PluginTag::getRegexSpaces('*');

		$a_tag        = RL_PluginTag::getRegexTags('a', false, false);
		$spans_images = RL_PluginTag::getRegexTags(['span', 'i', 'img']);
		$spans = RL_PluginTag::getRegexTags(['span', 'i']);
		$image = RL_PluginTag::getRegexTags('img', false, false, 'class');
		$any_text = '[^<>]*';

		self::$regexes = (object) [];

		self::$regexes->tag =
			'(?<start_pre>' . $pre . ')'
			. $tag_start . $params->tag . $spaces . '(?<data>' . $inside_tag . ')' . $tag_end
			. '(?<start_post>' . $post . ')'

			. '(?<pre>' . $pre . ')'
			. '(?<text>.*?)'
			. '(?<post>' . $post . ')'

			. '(?<end_pre>' . $pre . ')'
			. $tag_start . '\/' . $params->tag . $tag_end
			. '(?<end_post>' . $post . ')';

		self::$regexes->inlink =
			'(?<link_start>' . $a_tag . ')'
			. '(?<pre>' . $any_text . ')'

			. '(?<image_pre>(?:' . $spans_images . $any_text . '){0,6})'

			. $tag_start . $params->tag . $spaces_none . '(?<data>' . $inside_tag . ')' . $tag_end

			. '(?<text>.*?)'

			. $tag_start . '\/' . $params->tag . $tag_end

			. '(?<image_post>(?:' . $any_text . $spans_images . '){0,6})'

			. '(?<post>' . $any_text . ')'
			. '(?<link_end></a>)';

		self::$regexes->link = $a_tag;

		self::$regexes->image =
			'(?<link_start>(?:' . $a_tag . $any_text . '(?:' . $spans . $any_text . '){0,6})?)'
			. '(?<image>' . $image . ')'
			. '(?<link_end>(?:' . $any_text . '(?:' . $spans . $any_text . '){0,6}<\/a>)?)';

		self::$regexes->content =
			'(?<start_pre>' . $pre . ')'
			. $tag_start . $params->tag_content . '(?:\=|' . $spaces . ')+' . '(?<id>' . $inside_tag . ')' . $tag_end
			. '(?<start_post>' . $post . ')'

			. '(?<content>.*?)'

			. '(?<end_pre>' . $pre . ')'
			. $tag_start . '\/' . $params->tag_content . $tag_end
			. '(?<end_post>' . $post . ')';

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
