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
				return true;
			}
		}

		$params = Params::get();

		// allow in component?
		if (RL_Protect::isRestrictedComponent(isset($params->disabled_components) ? $params->disabled_components : [], $area))
		{
			if ( ! $params->disable_components_remove)
			{
				Protect::protectTags($string);

				return true;
			}

			Protect::_($string);

			$string = RL_RegEx::replace($params->regex, '', $string);

			RL_Protect::unprotect($string);

			return true;
		}

		Protect::_($string);

		self::replace($string);

		RL_Protect::unprotect($string);

		return true;
	}

	private static function replace(&$string)
	{
		list($start_tags, $end_tags) = Params::getTags();

		list($pre_string, $string, $post_string) = RL_Html::getContentContainingSearches(
			$string,
			$start_tags,
			$end_tags
		);

		if ($string == '')
		{
			$string = $pre_string . $string . $post_string;

			return;
		}

		$regex = Params::getRegex();
		RL_RegEx::matchAll($regex, $string, $matches);

		if (empty($matches))
		{
			$string = $pre_string . $string . $post_string;

			return;
		}

		foreach ($matches as $match)
		{
			$options = self::getOptions($match['data']);
			$text    = self::generate($options);

			list($pre, $post) = RL_Html::cleanSurroundingTags([$match['pre'], $match['post']]);

			$string = RL_String::replaceOnce($match[0], $pre . $text . $post, $string);
		}

		$string = $pre_string . $string . $post_string;
	}

	private static function getOptions($string = '')
	{
		$options = (object) [];

		$string = trim(str_replace(['&nbsp;', '&#160;'], '', $string));
		if ($string == '')
		{
			return $options;
		}

		// Convert old syntax
		self::convertOldSyntax($string);

		$known_boolean_keys = [
			'greyscale', 'dimensions',
		];

		// Get the values from the tag
		$attributes = RL_PluginTag::getAttributesFromString($string, 'type', $known_boolean_keys);

		if (isset($attributes->type))
		{
			$attributes->{$attributes->type} = 1;
			unset($attributes->type);
		}

		$key_aliases = [
			'image'       => ['i', 'img', 'images'],
			'paragraphs'  => ['p', 'paragraph'],
			'sentences'   => ['s', 'sentence'],
			'words'       => ['w', 'word'],
			'list'        => ['l', 'lists'],
			'title'       => ['t', 'titles'],
			'email'       => ['e', 'emails'],
			'kitchensink' => ['k', 'ks', 'kitchen', 'sink'],
			'service'     => ['image_service'],
			'list_type'   => ['listtype'],
		];

		RL_PluginTag::replaceKeyAliases($attributes, $key_aliases);

		return $attributes;
	}

	private static function convertOldSyntax(&$string)
	{
		if (empty($string))
		{
			return;
		}

		if (strpos($string, '"') !== false)
		{
			return;
		}

		if (strpos($string, '=') === false)
		{
			$string = 'type="' . $string . '"';

			return;
		}

		// Correct single attribute: p=5 => p="5"
		$string = preg_replace('#^([a-z0-9_\-]+)=([^\|]+)$#', '\1="\2"', $string);

		if (strpos($string, '"') !== false)
		{
			return;
		}

		// Correct multiple attributes: image|width=500|height=200 => type="image" width="500" height="200"
		$string = preg_replace('#=(.*?)\|#', '="\1" ', $string);
		$string = preg_replace('#" ([a-z0-9_\-]+)=([^\"])#', '" \1="\2', $string);
		$string = str_replace('|', ' ', $string);

		if (strpos($string, '"') !== false)
		{
			$string .= '"';
		}

		$string = preg_replace('#^([^ =]+) #', 'type="\1" ', $string);
	}

	private static function generate($options = '')
	{
		if (isset($options->image))
		{
			return self::generateImage($options);
		}

		return self::generateText($options);
	}

	private static function generateImage($options = '')
	{
		return Image::render($options);
	}

	private static function generateText($options = '')
	{
		$params = Params::get();

		$wordlist   = isset($options->wordlist) ? $options->wordlist : $params->wordlist;
		$diacritics = isset($options->diacritics) ? $options->diacritics : $params->diacritics;

		WordList::setType($wordlist);
		Diacritics::setType($diacritics);

		switch (true)
		{
			case (isset($options->kitchensink)) :
				$text = Text::kitchenSink();
				break;
			case (isset($options->paragraphs)) :
				$text = Text::paragraphs((int) $options->paragraphs);
				break;
			case (isset($options->sentences)) :
				$text = Text::sentences((int) $options->sentences);
				break;
			case (isset($options->words)) :
				$text = Text::words((int) $options->words);
				break;
			case (isset($options->list)) :
				$type = isset($options->list_type) ? $options->list_type : '';
				$text = Text::alist((int) $options->list, $type);
				break;
			case (isset($options->title)) :
				$text = Text::title((int) $options->title);
				break;
			case (isset($options->email)) :
				$text = Text::email();
				break;
			case ($params->type == 'list') :
				$text = Text::alist((int) $params->list_count, $params->list_type);
				break;
			default :
				$count = isset($params->{$params->type . '_count'}) ? $params->{$params->type . '_count'} : 1;

				$text = Text::byType($params->type, (int) $count);
				break;
		}

		return $text;
	}
}
