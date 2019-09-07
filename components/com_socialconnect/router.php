<?php
/**
 * @version     1.8.x
 * @package     SocialConnect
 * @author      JoomlaWorks http://www.joomlaworks.net
 * @copyright   Copyright (c) 2006 - 2016 JoomlaWorks Ltd. All rights reserved.
 * @license     http://www.joomlaworks.net/license
 */

defined('_JEXEC') or die ;

function SocialConnectBuildRoute(&$query)
{
	$segments = array();
	if (isset($query['view']))
	{
		$view = $query['view'];
		$segments[] = $view;
		unset($query['view']);
	}
	if (isset($query['task']))
	{
		$task = $query['task'];
		$segments[] = $task;
		unset($query['task']);
	}
	if (isset($query['return']))
	{
		$return = $query['return'];
		$segments[] = $return;
		unset($query['return']);
	}
	return $segments;
}

function SocialConnectParseRoute($segments)
{
	$vars = array();
	if ($segments[0] == 'login')
	{
		$vars['view'] = $segments[0];
		if (isset($segments[1]))
		{
			$vars['task'] = $segments[1];
		}
		if (isset($segments[2]))
		{
			$vars['return'] = $segments[2];
		}
	}
	else
	{
		$vars['task'] = $segments[0];
		if (isset($segments[1]))
		{
			$vars['return'] = $segments[1];
		}

	}
	return $vars;
}
