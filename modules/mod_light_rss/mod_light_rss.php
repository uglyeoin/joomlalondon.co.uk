<?php

/**
 * @package     Extly.Modules
 * @subpackage  mod_light_rss - Light RSS
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

defined('_JEXEC') or die;

if (!defined('AUTOTWEET_API'))
{
	include_once JPATH_ADMINISTRATOR . '/components/com_autotweet/api/autotweetapi.php';
}

// Include the helper functions only once
require_once dirname(__FILE__) . '/helper.php';

$enable_tooltip = ($params->get('enable_tooltip', 'yes') == 'yes');

// Get data from helper class
$light_rss = modLightRSSHelper::getFeed($params);

$rssrtl = false;

// Run default template script for output
require JModuleHelper::getLayoutPath('mod_light_rss');
