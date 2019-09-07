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



class VideosYoutube extends VideosObject
{
	var $url_prefix        = 'https://www.youtube.com/watch?v=';
	var $url_prefix_short  = 'https://youtu.be/';
	var $url_prefix_iframe = 'https://www.youtube.com/embed/';
	var $regex             = '(?:youtube\.com\/watch\?v=|youtu\.be/|youtube\.com/embed/)';

	protected function getContentVideoThumbUrl($video)
	{
		$id = $this->getIdFromUrl($video['url']);

		if ( ! $id)
		{
			return '';
		}

		return "https://img.youtube.com/vi/" . $id . "/mqdefault.jpg";
	}

	protected function getContentVideoThumbTag($video, $attributes)
	{
		$attributes->class = isset($attributes->class) ? $attributes->class : 'video-thumbnail-youtube';

		return parent::getContentVideoThumbTag($video, $attributes);
	}

	protected function getIframeUrl($url)
	{
		$url = parent::getIframeUrl($url);

		if (strpos($url, 'wmode=transparent') !== false)
		{
			return $url;
		}

		$url .= (strpos($url, '?') === false ? '?' : '&') . 'wmode=transparent';

		return $url;
	}
}
