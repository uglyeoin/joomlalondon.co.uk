<?php
/**
 * @version     1.8.x
 * @package     SocialConnect
 * @author      JoomlaWorks http://www.joomlaworks.net
 * @copyright   Copyright (c) 2006 - 2016 JoomlaWorks Ltd. All rights reserved.
 * @license     http://www.joomlaworks.net/license
 */

// no direct access
defined('_JEXEC') or die ;

JLoader::register('SocialConnectHelper', JPATH_SITE.'/components/com_socialconnect/helpers/socialconnect.php');
$user = JFactory::getUser();
SocialConnectHelper::loadHeadData($params, 'module');
SocialConnectHelper::setUserData($user);
$variables = SocialConnectHelper::setVariables($params);
foreach ($variables as $key => $value)
{
	$$key = $value;
}
$layout = ($user->guest) ? 'default' : 'authenticated';
$alignmentClass = $params->get('alignment', 'left') == 'right' ? 'socialConnectRight' : 'socialConnectLeft';
require (JModuleHelper::getLayoutPath('mod_socialconnect', $params->get('template', 'default').'/'.$layout));
