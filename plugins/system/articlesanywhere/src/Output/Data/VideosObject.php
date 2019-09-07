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

namespace RegularLabs\Plugin\System\ArticlesAnywhere\Output\Data;

defined('_JEXEC') or die;


use RegularLabs\Library\HtmlTag as RL_HtmlTag;
use RegularLabs\Library\RegEx as RL_RegEx;

class VideosObject extends Data
{
	var $regex_id = '[a-zA-Z0-9_-]+';

	public function getOutput($count, $data, $attributes)
	{
		if ( ! is_numeric($count))
		{
			return $this->getCount();
		}

		$videos = $this->getContentVideos();

		if (empty($videos))
		{
			return '';
		}

		// Subtract 1 off the count, as the videos array starts at 0
		$count = $count - 1;

		if ( ! isset($videos[$count]))
		{
			return '';
		}

		$video = $videos[$count];

		switch ($data)
		{
			case 'url':
				return $video['url'];

			case 'short-url':
			case 'url-short':
				return $this->getShortUrl($video['url']);

			case 'link':
				return '<a href="' . $video['url'] . '" target="_blank" class="video-link">';

			case 'iframe-url':
			case 'url-iframe':
				return $this->getIframeUrl($video['url']);

			case 'id':
				return $this->getIdFromUrl($video['url']);

			case 'thumb-url':
				return $this->getContentVideoThumbUrl($video);

			case 'thumb':
				return $this->getContentVideoThumbTag($video, $attributes);

			case '':
				return $this->getContentVideoTag($video, $attributes);

			default:
				return $this->getContentVideoAttribute($data, $video, $attributes);
		}
	}

	protected function getCount()
	{
		$videos = $this->getContentVideos();

		return count($videos);
	}

	protected function getContentVideoAttribute($key, $video, $attributes)
	{
		$type = 'main';
		if (strpos($key, 'thumb-') === 0)
		{
			$type = 'thumb';
			$key  = substr($key, 6);
		}

		$video_tag = $type == 'thumb'
			? $this->getContentVideoThumbTag($video, $attributes)
			: $this->getContentVideoTag($video, $attributes);

		$tag_attributes = RL_HtmlTag::getAttributes($video_tag);

		return isset($tag_attributes[$key]) ? $tag_attributes[$key] : '';
	}

	protected function getContentVideoThumbUrl($video)
	{
		return $video['url'];
	}

	protected function getContentVideoThumbTag($video, $attributes)
	{
		$attributes->src   = $this->getContentVideoThumbUrl($video);
		$attributes->class = isset($attributes->class) ? $attributes->class : 'video-thumbnail';

		Images::setAltAndTitle('video', $attributes);

		return Images::getImageHtml($attributes);
	}

	protected function getContentVideoTag($video, $attributes)
	{
		$url = $this->getIframeUrl($video['url']);
		$tag = str_replace($video['url'], $url, $video['tag']);

		return self::getVideoHtmlWithAttributes($tag, $attributes);
	}

	protected function getContentVideos()
	{
		// use static::class instead of get_class($this) after php 5.4 support is dropped
		$classname = basename(str_replace('\\', '/', get_class($this)));

		$videos = $this->item->get('_' . $classname);

		if ( ! is_null($videos) && is_array($videos))
		{
			return $videos;
		}

		$text = $this->item->get('text');

		RL_RegEx::matchAll($this->getRegExIframe(), $text, $videos);

		foreach ($videos as &$video)
		{
			$url = $this->fixUrl($video['url']);

			$video['tag'] = str_replace($video['url'], $url, $video['tag']);
			$video['url'] = $url;
		}

		$this->item->set('_' . $classname, $videos);

		return $videos;
	}

	protected function getIdFromUrl($url)
	{
		$url = $this->fixUrl($url);

		if ( ! RL_RegEx::match($this->getRegExIdQuery(), $url, $match))
		{
			return false;
		}

		return $match['id'];
	}

	static protected function getVideoHtmlWithAttributes($tag, $attributes)
	{
		if (empty($attributes))
		{
			return $tag;
		}

		$tag_attributes = RL_HtmlTag::getAttributes($tag);
		$tag_attributes = array_merge($tag_attributes, (array) $attributes);

		return '<iframe ' . RL_HtmlTag::flattenAttributes($tag_attributes) . '></iframe>';
	}

	protected function fixUrl($url)
	{
		$url = trim($url);

		if ( ! RL_RegEx::match($this->getRegExIdQuery(), trim($url), $match))
		{
			return $url;
		}

		$url = $this->url_prefix . $match['id'];

		if ( ! empty($match['query']))
		{
			$url .= '?' . $match['query'];
		}

		return $url;
	}

	protected function getIframeUrl($url)
	{
		return str_replace($this->url_prefix, $this->url_prefix_iframe, $url);
	}

	protected function getShortUrl($url)
	{
		return str_replace($this->url_prefix, $this->url_prefix_short, $url);
	}

	protected function getRegExIframe()
	{
		return '(?<tag><iframe\s[^>]*src=([\'"])(?<url>[^\'"]*' . $this->regex . '[^\'"]*)\2.*?</iframe>)';
	}

	protected function getRegExIdQuery()
	{
		return $this->regex . '(?<id>' . $this->regex_id . ')(?<query>.*)$';
	}
}
