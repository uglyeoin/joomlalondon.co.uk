<?php
/**
 * @package         Tooltips
 * @version         7.4.1PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2019 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\Tooltips;

defined('_JEXEC') or die;

use Joomla\CMS\Router\Route as JRoute;
use Joomla\CMS\Uri\Uri as JUri;
use RegularLabs\Library\Html as RL_Html;
use RegularLabs\Library\PluginTag as RL_PluginTag;
use RegularLabs\Library\Protect as RL_Protect;
use RegularLabs\Library\RegEx as RL_RegEx;
use RegularLabs\Library\StringHelper as RL_String;

class Replace
{
	public static function replaceTags(&$string, $area = 'article', $context = '')
	{
		if ( ! is_string($string) || $string == '')
		{
			return false;
		}

		if ( ! RL_String::contains($string, Params::getTags(true)))
		{
			return false;
		}

		// Check if tags are in the text snippet used for the search component
		if (strpos($context, 'com_search.') === 0)
		{
			$limit = explode('.', $context, 2);
			$limit = (int) array_pop($limit);

			$string_check = substr($string, 0, $limit);

			if ( ! RL_String::contains($string_check, Params::getTags(true)))
			{
				return false;
			}
		}

		$params = Params::get();
		$regex  = Params::getRegex();

		// allow in component?
		if (RL_Protect::isRestrictedComponent(isset($params->disabled_components) ? $params->disabled_components : [], $area))
		{
			if ( ! $params->disable_components_remove)
			{
				Protect::protectTags($string);

				return true;
			}

			Protect::_($string);

			$string = RL_RegEx::replace($regex, '\2', $string);

			RL_Protect::unprotect($string);

			return true;
		}

		Protect::_($string);

		list($start_tags, $end_tags) = Params::getTags();

		list($pre_string, $string, $post_string) = RL_Html::getContentContainingSearches(
			$string,
			$start_tags,
			$end_tags
		);

		RL_RegEx::matchAll($regex, $string, $matches);

		foreach ($matches as $match)
		{
			self::replaceTag($string, $match);
		}

		$string = $pre_string . $string . $post_string;

		RL_Protect::unprotect($string);

		return true;
	}

	private static function replaceTag(&$string, $match)
	{
		$params = Params::get();

		$tip  = self::getTip($match['tip']);
		$text = $match['text'];

		// Check if the text is an image
		if (RL_RegEx::match('^\s*<img [^>]*>\s*$', $text))
		{
			$tip->classes[] = 'isimg';
		}

		if ($tip->title)
		{
			$tip->classes_popover[] = 'has_title';
		}

		$template = '<div class="popover rl_tooltips nn_tooltips ' . implode(' ', $tip->classes_popover) . '"><div class="arrow"></div><div class="popover-inner"><h3 class="popover-title"></h3><div class="popover-content"><p></p></div></div></div>';

		$html = '<span'
			. ' class="rl_tooltips-link nn_tooltips-link ' . implode(' ', $tip->classes) . '"'
			. ' data-toggle="popover"'
			. ' data-html="true"'
			. ' data-template="' . self::makeSave($template) . '"'
			. ' data-placement="' . $tip->position . '"'
			. ' data-content="' . $tip->content . '"'
			. ' title="' . $tip->title . '">' . $text . '</span>';

		if (in_array('isimg', $tip->classes_popover))
		{
			// place the full image in a hidden span to make it pre-load it
			$html .= '<span style="display:none;">' . RL_String::html_entity_decoder($tip->content) . '</span>';
		}

		if ($params->place_comments)
		{
			$html = Protect::wrapInCommentTags($html);
		}

		$string = str_replace($match[0], $html, $string);
	}

	private static function getTip($string)
	{
		$tip = self::getTipFromSyntax($string);

		self::setDefaults($tip);

		self::setClasses($tip);

		self::prepareTextString($tip->title);
		self::prepareTextString($tip->content);

		return $tip;
	}

	private static function setDefaults(&$tip)
	{
		$defaults = [
			'title'           => '',
			'content'         => '',
			'classes_popover' => [],
			'classes'         => [],
		];

		foreach ($defaults as $key => $default)
		{
			if ( ! isset($tip->{$key}))
			{
				$tip->{$key} = $default;
				continue;
			}

			// Explode class strings
			if (is_array($default) && ! is_array($tip->{$key}))
			{
				$tip->{$key} = explode(' ', $tip->{$key});
			}
		}
	}

	private static function setClasses(&$tip)
	{
		$params = Params::get();

		if ( ! empty($tip->content) && RL_RegEx::match('^\s*(&lt;|<)img [^>]*(&gt;|>)\s*$', $tip->content))
		{
			$tip->classes_popover[] = 'isimg';
		}

		if ( ! empty($tip->image))
		{
			$attributes = self::getImageAttributes($tip);

			$tip->content           = '<img src="' . JRoute::_($tip->image) . '"' . $attributes . ' />';
			$tip->classes_popover[] = 'isimg';

			unset($tip->image);
		}

		if (empty($tip->title))
		{
			$tip->classes_popover[] = 'notitle';
		}

		if (empty($tip->content))
		{
			$tip->classes_popover[] = 'nocontent';
		}

		$tip->classes = array_diff($tip->classes, ['hover', 'sticky', 'click']);
		$tip->classes = array_diff($tip->classes, ['left', 'right', 'top', 'bottom']);

		$tip->mode     = isset($tip->mode) ? $tip->mode : $params->mode;
		$tip->position = isset($tip->position) ? $tip->position : $params->position;

		$tip->classes[] = $tip->mode;
		$tip->classes[] = $tip->position;

		return;
	}

	private static function getImageAttributes(&$tip)
	{
		$attributes = [];

		if ( ! empty($tip->image_attributes))
		{
			$attributes[] = $tip->image_attributes;
			unset($tip->image_attributes);
		}

		foreach ($tip as $key => $value)
		{
			if (strpos($key, 'image_') !== 0)
			{
				continue;
			}

			$attributes[] = substr($key, 6) . '="' . $value . '"';
			unset($tip->{$key});
		}

		return ! empty($attributes) ? ' ' . implode(' ', $attributes) : '';
	}

	private static function prepareTextString(&$string)
	{
		$string = self::fixUrls($string);
		$string = self::makeSave($string);
	}

	private static function fixUrls($string)
	{
		if (empty($string) || strpos($string, '="') === false)
		{
			return $string;
		}

		// JRoute internal links
		RL_RegEx::matchAll('href="([^"]*)"', $string, $url_matches);

		if ( ! empty($url_matches))
		{
			foreach ($url_matches as $url_match)
			{
				$url    = 'href="' . JRoute::_($url_match[1]) . '"';
				$string = str_replace($url_match[0], $url, $string);
			}
		}

		// Add root to internal image sources
		RL_RegEx::matchAll('src="([^"]*)"', $string, $url_matches);

		if ( ! empty($url_matches))
		{
			foreach ($url_matches as $url_match)
			{
				$url = $url_match[1];

				if (strpos($url, 'http') !== 0)
				{
					$url = JUri::root() . $url;
				}

				$url    = 'src="' . $url . '"';
				$string = str_replace($url_match[0], $url, $string);
			}
		}

		return $string;
	}

	private static function getTipFromSyntax($string)
	{
		// Convert WYSIWYG image html style to html
		if (strpos($string, '&lt;img'))
		{
			$string = RL_RegEx::replace('&lt;(img.+?)&gt;', '<\1>', $string);
		}

		if (strpos($string, '::') !== false || strpos($string, '|') !== false)
		{
			return self::getTipFromOldSyntax($string);
		}

		// Get the values from the tag
		$tag = RL_PluginTag::getAttributesFromString($string, 'content');

		$key_aliases = [
			'title'    => ['header', 'heading'],
			'content'  => ['tip', 'text', 'description'],
			'position' => ['pos'],
			'classes'  => ['class'],
		];

		RL_PluginTag::replaceKeyAliases($tag, $key_aliases);

		$tag->classes_popover = isset($tag->classes) ? $tag->classes : [];

		return $tag;
	}

	private static function getTipFromOldSyntax($string)
	{
		$params = Params::get();

		$classes = str_replace('\|', '[:TT_BAR:]', $string);
		$classes = explode('|', $classes);
		foreach ($classes as $i => $class)
		{
			$classes[$i] = trim(str_replace('[:TT_BAR:]', '|', $class));
		}
		$string = array_shift($classes);

		$classes_popover = $classes;

		$mode = 'hover';
		$mode = in_array('click', $classes) ? 'click' : $mode;
		$mode = in_array('sticky', $classes) ? 'sticky' : $mode;

		
		$position = $params->position;
		$position = in_array('left', $classes) ? 'left' : $position;
		$position = in_array('right', $classes) ? 'right' : $position;
		$position = in_array('top', $classes) ? 'top' : $position;
		$position = in_array('bottom', $classes) ? 'bottom' : $position;

		$tip = explode('::', $string, 2);

		$title   = isset($tip[1]) ? $tip[0] : '';
		$content = isset($tip[1]) ? $tip[1] : $tip[0];

		return (object) [
			'title'           => $title,
			'content'         => $content,
			'classes_popover' => $classes_popover,
			'classes'         => $classes,
			'mode'            => $mode,
			'position'        => $position,
		];
	}

	private static function makeSave($string)
	{
		if (strpos($string, '&lt;img') === false)
		{
			// convert & to html entities
			// If string contains an <img> tag, interpret as html
			$string = str_replace('&', '&amp;', $string);
		}

		return str_replace(['"', '<', '>'], ['&quot;', '&lt;', '&gt;'], $string);
	}
}
