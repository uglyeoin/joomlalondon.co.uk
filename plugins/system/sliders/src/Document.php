<?php
/**
 * @package         Sliders
 * @version         7.7.6PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2019 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\Sliders;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\HTML\HTMLHelper as JHtml;
use RegularLabs\Library\Document as RL_Document;

class Document
{
	public static function addHeadStuff()
	{
		// do not load scripts/styles on feeds or print pages
		if (RL_Document::isFeed() || JFactory::getApplication()->input->getInt('print', 0))
		{
			return;
		}

		$params = Params::get();

		if ( ! $params->load_bootstrap_framework && $params->load_jquery)
		{
			JHtml::_('jquery.framework');
		}

		if ($params->load_bootstrap_framework)
		{
			JHtml::_('bootstrap.framework');
		}

		if ($params->use_cookies || $params->set_cookies)
		{
			RL_Document::script('regularlabs/jquery.cookie.min.js');
		}

		$options = [
			'use_hash'       => (int) $params->use_hash,
			'reload_iframes' => (int) $params->reload_iframes,
			'init_timeout'   => (int) $params->init_timeout,
			'mode'           => $params->mode ?: 'click',
			'use_cookies'    => (int) $params->use_cookies,
			'set_cookies'    => (int) $params->set_cookies,
			'cookie_name'    => $params->cookie_name,
			'scroll'         => (int) $params->scroll,
			'linkscroll'     => (int) $params->linkscroll,
			'urlscroll'      => (int) $params->urlscroll,
			'scrolloffset'   => (int) $params->scrolloffset,
		];

		RL_Document::scriptOptions($options, 'Sliders');

		RL_Document::script('sliders/script.min.js', ($params->media_versioning ? '7.7.6.p' : ''));

		if ($params->load_stylesheet)
		{
			RL_Document::style('sliders/style.min.css', ($params->media_versioning ? '7.7.6.p' : ''));
		}

		$style = '';
		if ($params->slide_speed != 350)
		{
			$style .= '
				.rl_sliders.has_effects .collapse {
				  -webkit-transition-duration: ' . $params->slide_speed . 'ms;
				  -moz-transition-duration: ' . $params->slide_speed . 'ms;
				  -o-transition-duration: ' . $params->slide_speed . 'ms;
				  transition-duration: ' . $params->slide_speed . 'ms;
				}
			';
		}

		if ($params->scrolloffset)
		{
			$style .= '
				.rl_sliders-scroll {
					top: ' . $params->scrolloffset . 'px;
				}
			';
		}

		if ( ! $style)
		{
			return;
		}

		RL_Document::styleDeclaration($style, 'Sliders');
	}

	public static function removeHeadStuff(&$html)
	{
		// Don't remove if sliders id is found
		if (strpos($html, 'id="set-rl_sliders') !== false)
		{
			return;
		}

		// remove style and script if no items are found
		RL_Document::removeScriptsStyles($html, 'Sliders');
		RL_Document::removeScriptsOptions($html, 'Sliders');
	}
}
