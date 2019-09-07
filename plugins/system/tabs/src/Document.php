<?php
/**
 * @package         Tabs
 * @version         7.5.9PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2019 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\Tabs;

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
			'use_hash'                => (int) $params->use_hash,
			'reload_iframes'          => (int) $params->reload_iframes,
			'init_timeout'            => (int) $params->init_timeout,
			'mode'                    => $params->mode ?: 'click',
			'use_cookies'             => (int) $params->use_cookies,
			'set_cookies'             => (int) $params->set_cookies,
			'cookie_name'             => $params->cookie_name,
			'scroll'                  => (int) $params->scroll,
			'linkscroll'              => (int) $params->linkscroll,
			'urlscroll'               => (int) $params->urlscroll,
			//'scrolloffset'            => (int) $params->scrolloffset,
			//'scrolloffset_sm'         => (int) $params->scrolloffset_sm,
			'slideshow_timeout'       => (int) $params->slideshow_timeout,
			//'use_responsive_view'     => (int) $params->use_responsive_view,
			'stop_slideshow_on_click' => (int) $params->stop_slideshow_on_click,
		];

		RL_Document::scriptOptions($options, 'Tabs');

		RL_Document::script('tabs/script.min.js', ($params->media_versioning ? '7.5.9.p' : ''));

		if ($params->load_stylesheet)
		{
			RL_Document::stylesheet('tabs/style.min.css', ($params->media_versioning ? '7.5.9.p' : ''));
		}

		$style = '';
		if ($params->scrolloffset)
		{
			$style .= '
				.rl_tabs-scroll {
					top: ' . $params->scrolloffset . 'px;
				}
			';
		}
		if ($params->scrolloffset_sm)
		{
			$style .= '
				.rl_tabs-sm-scroll {
					top: ' . $params->scrolloffset_sm . 'px;
				}
			';
		}

		if ( ! $style)
		{
			return;
		}

		RL_Document::styleDeclaration($style, 'Tabs');
	}

	public static function removeHeadStuff(&$html)
	{
		// Don't remove if tabs class is found
		if (strpos($html, 'class="rl_tabs-tab') !== false)
		{
			return;
		}

		// remove style and script if no items are found
		RL_Document::removeScriptsStyles($html, 'Tabs');
		RL_Document::removeScriptsOptions($html, 'Tabs');
	}
}
