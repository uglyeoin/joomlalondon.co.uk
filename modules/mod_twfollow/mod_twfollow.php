<?php

/**
 * @package     Extly.Modules
 * @subpackage  mod_twfollow - This module shows a Twitter Stream.
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

// Include the syndicate functions only once
require_once dirname(__FILE__) . '/helper.php';

$twData = ModTwfollowHelper::getTwitterData($params);
require JModuleHelper::getLayoutPath('mod_twfollow');
