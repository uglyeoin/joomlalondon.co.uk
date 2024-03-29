<?php
/**
 * @package         Articles Anywhere
 * @version         9.3.5PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2019 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\ArticlesAnywhere;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text as JText;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Html as RL_Html;
use RegularLabs\Library\Protect as RL_Protect;
use RegularLabs\Library\RegEx as RL_RegEx;
use RegularLabs\Library\StringHelper as RL_String;
use RegularLabs\Plugin\System\ArticlesAnywhere\PluginTags\PluginTag;
use RegularLabs\Plugin\System\ArticlesAnywhere\PluginTags\PluginTags;

class Replace
{
	static $message = '';

	public static function replaceTags(&$string, $area = 'article', $context = '', $article = null)
	{
		if ( ! is_string($string) || $string == '')
		{
			return false;
		}

		if ( ! RL_String::contains($string, Params::getTags(true)))
		{
			return false;
		}

		if ($area == 'article')
		{
			CurrentArticle::set($article);
		}

		$params = Params::get();

		self::$message = '';

		if ($area == 'article' && ! RL_Protect::articlePassesSecurity($article, $params->articles_security_level))
		{
			self::$message = JText::_('AA_OUTPUT_REMOVED_SECURITY');
		}

		// allow in component?
		if (RL_Protect::isRestrictedComponent(isset($params->disabled_components) ? $params->disabled_components : [], $area))
		{
			if ( ! $params->disable_components_remove)
			{
				Protect::protectTags($string);

				return true;
			}

			self::$message = JText::_('AA_OUTPUT_REMOVED_NOT_ENABLED');

			Protect::_($string);
		}

		Protect::_($string);

		switch ($area)
		{
			case 'article':
				$replace = self::prepareStringForArticles($string, $context);
				break;

			case 'component':
				$replace = self::prepareStringForComponent($string);
				break;

			default:
			case 'body':
				$replace = self::prepareStringForBody($string);
				break;
		}

		if ($replace)
		{
			self::process($string);
		}

		RL_Protect::unprotect($string);

		return true;
	}

	private static function prepareStringForArticles(&$string, $context = '')
	{
		$params = Params::get();

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

		if ( ! $params->articles_enable)
		{
			self::$message = JText::_('AA_OUTPUT_REMOVED_NOT_ENABLED');
		}

		return true;
	}

	private static function prepareStringForComponent(&$string)
	{
		$params = Params::get();

		if ( ! $params->components_enable)
		{
			self::$message = JText::_('AA_OUTPUT_REMOVED_NOT_ENABLED');
		}

		if (RL_Document::isFeed())
		{
			$s      = '(<item[^>]*>)';
			$string = RL_RegEx::replace($s, '\1<!-- START: AA_COMPONENT -->', $string);
			$string = str_replace('</item>', '<!-- END: AA_COMPONENT --></item>', $string);
		}

		if (strpos($string, '<!-- START: AA_COMPONENT -->') === false)
		{
			Area::tag($string, 'component');
		}

		$components = Area::get($string, 'component');

		foreach ($components as $component)
		{
			if (strpos($string, $component[0]) === false)
			{
				continue;
			}

			self::process($component[0]);
			$string = str_replace($component[0], $component[0], $string);
		}

		return false;
	}

	private static function prepareStringForBody(&$string)
	{
		$params = Params::get();

		if ( ! $params->other_enable)
		{
			self::$message = JText::_('AA_OUTPUT_REMOVED_NOT_ENABLED');
		}

		return true;
	}

	public static function process(&$full_string)
	{
		list($start_tags, $end_tags) = Params::getTags();

		list($pre_string, $string, $post_string) = RL_Html::getContentContainingSearches(
			$full_string,
			$start_tags,
			$end_tags
		);

		$pluginTags = new PluginTags;

		$tags = $pluginTags->get($string);

		if (empty($tags))
		{
			return;
		}

		$break     = 0;
		$max_loops = 10;

		while (
			$break++ < $max_loops
			&& ! empty($tags)
		)
		{
			self::replaceTagsInString($string, $tags);

			$tags = $pluginTags->get($string);
		}

		$full_string = $pre_string . $string . $post_string;
	}

	private static function replaceTagsInString(&$string, $tags)
	{
		/** @var PluginTag $tag */
		foreach ($tags as $tag)
		{
			$output = self::$message ? Protect::getMessageCommentTag(self::$message) : $tag->getOutput();

			$string = RL_String::replaceOnce($tag->getOriginalString(), $output, $string);
		}
	}
}
