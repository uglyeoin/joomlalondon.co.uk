<?php

/**
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

defined('_JEXEC') or die;

// Check for PHP4
if (defined('PHP_VERSION'))
{
	$version = PHP_VERSION;
}
elseif (function_exists('phpversion'))
{
	$version = phpversion();
}
else
{
	// No version info. I'll lie and hope for the best.
	$version = '5.0.0';
}

// Old PHP version detected. EJECT! EJECT! EJECT!
if (!version_compare($version, '5.4.0', '>='))
{
	return JError::raise(
			E_ERROR,
			500,
			'PHP versions 4.x, 5.0, 5.1 and 5.2 are no longer supported by AutoTweetNG.',
			'The version of PHP used on your site is obsolete and contains known security vulenrabilities.
			Moreover, it is missing features required by AutoTweetNG to work properly or at all.
			Please ask your host to upgrade your server to the latest PHP 5.3/5.4 stable release. Thank you!');
}

if (!defined('AUTOTWEET_API'))
{
	include_once JPATH_ADMINISTRATOR . '/components/com_autotweet/api/autotweetapi.php';
}

// Composer Prefixed Libraries
require_once JPATH_ROOT . '/autoload.php';

$config = array();

$view = null;

// If we are processing Gplus, redirect to controller
$session = JFactory::getSession();

// If we are processing Frontend Twitter Channel Auth, redirect to controller
$authstate = $session->get('twitter-authstate');

if ($authstate)
{
	$session->set('twitter-authstate', 0);

	$view = 'userchannels';
	$config['input'] = array('task' => 'twCallback');
}

// If we are processing Frontend LinkedIn Channel Auth, redirect to controller
$authstate = $session->get('linkedin-authstate');

if ($authstate)
{
	$session->set('linkedin-authstate', 0);

	$view = 'userchannels';
	$config['input'] = array('task' => 'liOAuth2Callback');
}

// F0F app
if (AUTOTWEETNG_JOOCIAL)
{
	try
	{
		F0FDispatcher::getTmpInstance('com_autotweet', $view, $config)->dispatch();
	}
	catch (Exception $e)
	{
		JFactory::getApplication()->enqueueMessage($e->getMessage());
		JFactory::getApplication()->redirect(JRoute::_('index.php?option=com_autotweet&view=notauths'));

		$logger = AutotweetLogger::getInstance();
		$logger->log(JLog::ERROR, "NOT-AUTHS:", $e->getMessage());
	}
}
else
{
	F0FDispatcher::getTmpInstance('com_autotweet', $view, $config)->dispatch();
}
