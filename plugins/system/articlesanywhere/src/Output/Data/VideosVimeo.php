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



class VideosVimeo extends VideosObject
{
	var $url_prefix        = 'https://vimeo.com/';
	var $url_prefix_short  = 'https://vimeo.com/';
	var $url_prefix_iframe = 'https://player.vimeo.com/video/';
	var $regex             = '(?:vimeo\.com/|player\.vimeo\.com/video/)';
	var $regex_id          = '[0-9]';

	protected function getContentVideoThumbUrl($video)
	{
		return '';
	}

	protected function getContentVideoThumbTag($video, $attributes)
	{
		return '';
	}
}
