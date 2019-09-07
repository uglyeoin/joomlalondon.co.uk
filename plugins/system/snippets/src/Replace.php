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

use Joomla\CMS\Language\Text as JText;
use RegularLabs\Library\Html as RL_Html;
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

			$string = RL_RegEx::replace($regex, '', $string);

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

		$break_count = 0;
		while ($break_count++ < 20
			&& ! empty($matches))
		{
			self::renderSnippets($string, $matches);

			RL_RegEx::matchAll($regex, $string, $matches);
		}

		$string = $pre_string . $string . $post_string;

		RL_Protect::unprotect($string);

		if (Params::get()->fix_html && $area != 'head')
		{
			$string = RL_Html::fix($string);
		}

		return true;
	}

	private static function renderSnippets(&$string, $matches)
	{
		foreach ($matches as $match)
		{
			$output = self::renderSnippet($match);

			$string = RL_String::replaceOnce($match[0], $output, $string);
		}
	}

	private static function renderSnippet($data)
	{
		$params = Params::get();

		$id   = trim($data['id']);
		$vars = '';

		if (strpos($id, '|'))
		{
			list($id, $vars) = explode('|', $id, 2);
		}

		$content = self::processSnippet(trim($id), trim($vars));

		if ($params->place_comments)
		{
			$content = Protect::wrapInCommentTags($content);
		}

		$same_surrounding_tags = isset($data['pretag']) && isset($data['posttag']) && $data['pretag'] == $data['posttag'];

		if ( ! RL_Html::containsBlockElements($content) || ! $same_surrounding_tags || ! $params->strip_surrounding_tags)
		{
			$content = $data['pre'] . $content . $data['post'];
		}

		return $content;
	}

	private static function processSnippet($id, $vars)
	{
		$params = Params::get();
		$item   = Items::get($id);

		if ( ! $item)
		{
			if ($params->place_comments)
			{
				return Protect::getMessageCommentTag(JText::_('SNP_OUTPUT_REMOVED_NOT_FOUND'));
			}

			return '';
		}

		if ( ! $item->published)
		{
			if ($params->place_comments)
			{
				return Protect::getMessageCommentTag(JText::_('SNP_OUTPUT_REMOVED_NOT_ENABLED'));
			}

			return '';
		}

		$html = $item->content;

		if ($vars != '')
		{
			$unprotected = ['\\|', '\\{', '\\}'];
			$protected   = RL_Protect::protectArray($unprotected);
			RL_Protect::protectInString($vars, $unprotected, $protected);

			$vars = explode('|', $vars);

			foreach ($vars as $i => $var)
			{
				RL_Protect::unprotectInString($var, ['|', '{', '}'], $protected);
				$html = RL_RegEx::replace('\\\\' . ($i + 1) . '(?![0-9])', $var, $html);
			}
		}

		if (strpos($html, '[[escape]]') !== false
			&& RL_RegEx::matchAll('\[\[escape\]\](.*?)\[\[/escape\]\]', $html, $matches)
		)
		{
			foreach ($matches as $match)
			{
				$replace = addslashes($match[1]);
				$html    = str_replace($match[0], $replace, $html);
			}
		}

		if ($item->remove_paragraphs == 1 || $item->remove_paragraphs == -1 && $params->remove_paragraphs)
		{
			return self::removeParagraphs($html);
		}

		return $html;
	}

	private static function removeParagraphs($string)
	{
		// Remove leading paragraph tags
		$string = RL_RegEx::replace('^(\s*</?p[^>]*>)+', '', $string);
		// Remove trailing paragraph tags
		$string = RL_RegEx::replace('(</?p[^>]*>\s*)+$', '', $string);
		// Replace paragraph tags with double breaks
		$string = RL_RegEx::replace('(</p>\s*<p[^>]*>|</?p[^>]*>)', '<br><br>', $string);

		return $string;
	}
}
