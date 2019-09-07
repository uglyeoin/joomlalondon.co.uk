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

use ContentHelperRoute;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use RegularLabs\Library\File as RL_File;
use RegularLabs\Library\PluginTag as RL_PluginTag;
use RegularLabs\Library\RegEx as RL_RegEx;
use RegularLabs\Library\StringHelper as RL_String;

class Link
{
	public static function build($attributes, $data, $content = '')
	{
		if (isset($data['gallery']) && strpos($data['gallery'], '/') !== false)
		{
			return Gallery::buildGallery($attributes, $data, $content);
		}

		if (isset($data['image']))
		{
			$attributes->href = $data['image'];
			unset($data['image']);

			if ( ! $content)
			{
				$image = new Image($attributes->href, $data);

				$content = $image->thumbnail->exists()
					? $image->thumbnail->getHtmlTag()
					: $image->getHtmlTag();

				$attributes->href = $image->getHref();

				if ( ! isset($attributes->title))
				{
					$attributes->title = $image->attributes->title;
				}

				if ( ! isset($attributes->alt))
				{
					$attributes->alt = $image->attributes->alt;
				}

				if ( ! isset($attributes->{'data-modal-description'}) && isset($image->attributes->description))
				{
					$attributes->{'data-modal-description'} = $image->attributes->description;
				}
			}
		}

		$params = Params::get();

		self::setVideoUrl($attributes, $data);

		if (empty($attributes->href))
		{
			return '';
		}

		$isexternal = RL_File::isExternal($attributes->href);
		$ismedia    = RL_File::isMedia($attributes->href, $params->mediafiles);
		$isvideo    = File::isVideo($attributes->href, $data);
		$fullpage   = (empty($data['fullpage']) || $isexternal) ? false : (bool) $data['fullpage'];
		$isiframe   = $fullpage || File::isIframe($attributes->href, $data);
		$class      = ! empty($data['classname']) ? [$data['classname']] : [];

		if (isset($attributes->{'data-modal-title'}) && ! isset($data['title']))
		{
			$data['title'] = $attributes->{'data-modal-title'};
			unset($attributes->{'data-modal-title'});
		}

		if (isset($attributes->title) && ! isset($data['title']))
		{
			$data['title'] = $attributes->title;
			unset($attributes->title);
		}

		if (isset($attributes->{'data-modal-description'}) && ! isset($data['description']))
		{
			$data['description'] = $attributes->{'data-modal-description'};
			unset($attributes->{'data-modal-description'});
		}

		if ($ismedia)
		{
			$class[]       = 'is_image';
			$data['image'] = 'true';

			if ( ! isset($data['title']))
			{
				$auto_titles = isset($data['auto_titles']) ? $data['auto_titles'] : $params->auto_titles;
				$title_case  = isset($data['title_case']) ? $data['title_case'] : $params->title_case;
				if ($auto_titles)
				{
					$data['title'] = File::getTitle($attributes->href, $title_case);
				}
			}

			if ($params->retinaurl && ! $isexternal && ! File::retinaImageExists($attributes->href))
			{
				$data['retinaurl'] = 'false';
			}
		}
		unset($data['auto_titles']);

		// Force/overrule certain data values
		if ($isiframe || ($isexternal && ! $ismedia))
		{
			// use iframe mode for external urls
			$data['iframe'] = 'true';
			Data::setDataWidthHeight($data, $isexternal);
		}

		if ($isvideo)
		{
			$class[]       = 'is_video';
			$data['video'] = 'true';
		}

		if ($attributes->href && $attributes->href[0] != '#' && ! $isexternal && ! $ismedia && ! $isvideo)
		{
			$attributes->href = Document::addUrlAttributes($attributes->href, $isiframe, $fullpage, ! empty($data['print']));
		}


		// Set open value based on sessions with openMin / openMax
		Data::setDataOpen($data, $attributes);

		if (empty($data['group']) && $params->auto_group && RL_RegEx::match($params->auto_group_filter, $attributes->href, $match, ''))
		{
			$data['group'] = $params->auto_group_id;
		}

		if ( ! empty($data['description']))
		{
			$data['title'] = empty($data['title']) ? '' : $data['title'];
			$data['title'] .= '<div class="modals_description">' . $data['description'] . '</div>';
			unset($data['description']);
		}

		if (isset($data['navigation']) && ! $data['navigation'])
		{
			$class[] = 'no_navigation';
			unset($data['navigation']);
		}

		if (empty($data['title']))
		{
			$class[]       = 'no_title';
			$data['title'] = '';
		}

		$data['classname'] = implode(' ', $class);

		$show_countdown = isset($data['countdown']) ? $data['countdown'] : $params->countdown;
		if ( ! empty($data['autoclose']) && $show_countdown)
		{
			$data['title'] .= '<div class="countdown"></div>';
		}

		// Add aria label for empty links for accessibility
		if (empty($content))
		{
			$label = isset($attributes->title)
				? $attributes->title
				: (isset($data['title'])
					? self::cleanTitle($data['title'])
					: ''
				);

			$attributes->{'aria-label'} = $label ?: 'Popup link';
		}

		return
			'<a'
			. Data::flattenAttributeList($attributes)
			. Data::flattenDataAttributeList($data)
			. '>'
			. $content;
	}

	private static function cleanTitle($string)
	{
		$string = str_replace('<div class="modals_description">', ' - ', $string);

		return RL_String::removeHtml($string);
	}

	public static function get($string, $link = '', $content = '')
	{
		list($attributes, $data, $extra) = self::getData($string, $link);

		return [self::build($attributes, $data, $content), $extra];
	}

	public static function getData($string, $link = '')
	{
		$params = Params::get();

		$attributes = self::prepareAttributeList($link);

		RL_PluginTag::protectSpecialChars($string);

		$is_old_syntax =
			(strpos($string, '|') !== false)
			|| (strpos($string, '"') === false && strpos($string, '&quot;') === false);

		if ($is_old_syntax)
		{
			// Replace open attribute with open=1
			$string = RL_RegEx::replace('(^|\|)open($|\|)', '\1open=1\2', $string);

			// Add empty url attribute to beginning if no url/href attribute is there,
			// to prevent issues with grabbing values from old syntax
			if (RL_RegEx::match('^([a-z]+)=', $string, $match))
			{
				if ($match[1] != 'url' && $match[1] != 'href')
				{
					$string = 'url=|' . $string;
				}
			}
		}

		RL_PluginTag::unprotectSpecialChars($string);

		// Get the values from the tag
		$tag = RL_PluginTag::getAttributesFromString($string, 'url', $params->booleans);

		$key_aliases = [
			'url'              => ['href', 'link', 'src'],
			'image'            => ['img'],
			'gallery'          => ['galery', 'images'],
			'thumbnail'        => ['thumbnails', 'thumb', 'thumbs'],
			'createthumbnails' => ['createthumbs'],
			'thumbnailwidth'   => ['thumbwidth'],
			'thumbnailheight'  => ['thumbheight'],
			'thumbnailsuffix'  => ['thumbsuffix'],
			'thumbnailquality' => ['thumbquality'],
			'cookie'           => ['cookie_name', 'cookie_id'],
		];

		RL_PluginTag::replaceKeyAliases($tag, $key_aliases);

		if ( ! empty($tag->url))
		{
			$attributes->href = self::cleanUrl($tag->url);
		}
		unset($tag->url);

		if ( ! empty($tag->target))
		{
			$attributes->target = $tag->target;
		}
		unset($tag->target);

		$extra = '';

		// Handle the different tag attributes
		switch (true)
		{
			case ( ! empty($tag->article)):
				$id = $tag->article;

				$db    = JFactory::getDbo();
				$query = $db->getQuery(true)
					->select('a.id, a.catid')
					->from('#__content AS a');
				$where = 'a.title = ' . $db->quote(RL_String::html_entity_decoder($id));
				$where .= ' OR a.alias = ' . $db->quote(RL_String::html_entity_decoder($id));
				if (is_numeric($id))
				{
					$where .= ' OR a.id = ' . (int) $id;
				}
				$query->where('(' . $where . ')');
				$db->setQuery($query);
				$article = $db->loadObject();

				if ( ! $article)
				{
					$attributes->href = '#';
					unset($tag->article);
					break;
				}

				if ( ! class_exists('ContentHelperRoute'))
				{
					require_once JPATH_SITE . '/components/com_content/helpers/route.php';
				}

				$attributes->href = ContentHelperRoute::getArticleRoute($article->id, $article->catid);

				// Replace current active menu id with the default menu id
				$language     = JFactory::getLanguage()->getTag();
				$default_menu = JFactory::getApplication()->getMenu('site')->getDefault($language);
				$active_menu  = JFactory::getApplication()->getMenu('site')->getActive();

				if (isset($active_menu->id))
				{
					$attributes->href = RL_RegEx::replace('&Itemid=' . $active_menu->id . '$', '&Itemid=' . $default_menu->id, $attributes->href, '');
				}

				unset($tag->article);
				break;

			case ( ! empty($tag->html)):
				$id               = uniqid('modal_') . rand(1000, 9999);
				$extra            = '<div style="display:none;"><div id="' . $id . '">'
					. $tag->html
					. '</div></div>';
				$attributes->href = '#' . $id;
				unset($tag->html);
				break;

			case ( ! empty($tag->content)):
				$content_id       = trim(str_replace(['"', "'", '#'], '', $tag->content));
				$attributes->href = '#' . $content_id;
				unset($tag->content);
				break;

			case ( ! empty($tag->gallery)):
				$attributes->href = '#';
				break;
		}

		$attributes->id = ! empty($tag->id) ? $tag->id : '';
		unset($tag->id);

		$attributes->class .= ! empty($tag->class) ? ' ' . $tag->class : '';
		unset($tag->class);

		if ( ! empty($tag->title))
		{
			$tag->title        = self::translateString($tag->title);
			$attributes->title = RL_String::removeHtml($tag->title);
		}

		if ( ! empty($tag->description))
		{
			$tag->description = self::translateString($tag->description);
		}

		// move onSomething params to attributes, except the modal callbacks
		$callbacks = ['onopen', 'onload', 'oncomplete', 'oncleanup', 'onclosed'];
		foreach ($tag as $key => $val)
		{
			if (
				substr($key, 0, 2) == 'on'
				&& ! in_array(strtolower($key), $callbacks)
				&& is_string($val)
			)
			{
				$attributes->{$key} = $val;
				unset($tag->{$key});
			}
		}

		$data = [];

		// set data defaults
		if ($attributes->href)
		{
			if ($attributes->href[0] == '#')
			{
				$data['inline'] = 'true';
			}
			elseif ($attributes->href == '-html-')
			{
				$attributes->href = '#';
			}
		}

		// set data by values set in tag
		foreach ($tag as $key => $val)
		{
			$data[strtolower($key)] = $val;
		}

		return [$attributes, $data, $extra];
	}

	private static function translateString($string = '')
	{
		if (empty($string) || ! RL_RegEx::match('^[A-Z][A-Z0-9_]+$', $string))
		{
			return $string;
		}

		return JText::_($string);
	}

	private static function prepareAttributeList($link)
	{
		$params = Params::get();

		$attributes        = (object) [];
		$attributes->href  = '';
		$attributes->class = $params->class;
		$attributes->id    = '';

		if ( ! $link)
		{
			return $attributes;
		}

		$link_attributes = self::getAttributeList(trim($link));

		foreach ($link_attributes as $key => $val)
		{
			$key = trim($key);
			$val = trim($val);

			if ($key == '' || $val == '')
			{
				continue;
			}

			if ($key == 'class')
			{
				$attributes->{$key} = trim($attributes->{$key} . ' ' . $val);
				continue;
			}

			$attributes->{$key} = $val;
		}

		return $attributes;
	}

	public static function getAttributeList($string)
	{
		$attributes = (object) [];

		if ( ! $string)
		{
			return $attributes;
		}

		$params = Params::get();

		RL_RegEx::matchAll('([a-z0-9_-]+)\s*=\s*(?:"(.*?)"|\'(.*?)\')', $string, $params);

		if (empty($params))
		{
			return $attributes;
		}

		foreach ($params as $param)
		{
			$attributes->{$param[1]} = isset($param[3]) ? $param[3] : $param[2];
		}

		return $attributes;
	}

	private static function cleanUrl($url)
	{
		return RL_RegEx::replace('<a[^>]*>(.*?)</a>', '\1', $url);
	}

	private static function setVideoUrl(&$attributes, &$data)
	{
		if (isset($data['youtube']))
		{
			$attributes->href = self::fixUrlYoutube('youtube=' . $data['youtube']);

			if ( ! empty($data['autoplay']))
			{
				$attributes->href = self::addUrlParameter($attributes->href, 'autoplay', '1');
			}

			unset($data['youtube']);
			unset($data['autoplay']);

			$data['video'] = 'true';

			return;
		}

		if (isset($data['vimeo']))
		{
			$attributes->href = self::fixUrlVimeo('vimeo=' . $data['vimeo']);

			if ( ! empty($data['autoplay']))
			{
				$attributes->href = self::addUrlParameter($attributes->href, 'autoplay', '1');
			}

			unset($data['vimeo']);
			unset($data['autoplay']);

			$data['video'] = 'true';

			return;
		}

		$attributes->href = self::fixVideoUrl($attributes->href, $data);
	}

	private static function fixVideoUrl($url, &$data)
	{
		switch (true)
		{
			case(
				strpos($url, 'youtu.be') !== false
				|| strpos($url, 'youtube.com') !== false
				|| strpos($url, 'youtube=') !== false
			) :
				$data['video'] = 'true';

				return self::fixUrlYoutube($url);

			case(
				strpos($url, 'vimeo.com') !== false
				|| strpos($url, 'vimeo=') !== false
			) :
				$data['video'] = 'true';

				return self::fixUrlVimeo($url);
		}

		return $url;
	}

	private static function fixUrlYoutube($url)
	{
		$regex = '(?:^youtube=|youtu\.be/?|youtube\.com/embed/?|youtube\.com\/watch\?v=)(?<id>[^/&\?]+)(?:\?|&amp;|&)?(?<query>.*)$';

		if ( ! RL_RegEx::match($regex, trim($url), $match))
		{
			return $url;
		}

		$url = 'https://www.youtube.com/embed/' . $match['id'];

		$url = self::addUrlParameter($url, $match['query']);
		$url = self::addUrlParameter($url, 'wmode', 'transparent');

		return $url;
	}

	private static function fixUrlVimeo($url)
	{
		$regex = '(?:^vimeo=|vimeo\.com/(?:video/)?)(?<id>[0-9]+)(?<query>.*)$';

		if ( ! RL_RegEx::match($regex, trim($url), $match))
		{
			return $url;
		}

		$url = 'https://player.vimeo.com/video/' . $match['id'];

		$url = self::addUrlParameter($url, $match['query']);

		return $url;
	}

	private static function addUrlParameter($url, $key, $value = '')
	{
		if (empty($key))
		{
			return $url;
		}

		$key = ltrim($key, '?&');

		if (RL_RegEx::match('[\?&]' . $key . '=', $url))
		{
			return $url;
		}

		$query = $key;

		if ($value)
		{
			$query .= '=' . $value;
		}

		return $url . (strpos($url, '?') === false ? '?' : '&') . $query;
	}
}
