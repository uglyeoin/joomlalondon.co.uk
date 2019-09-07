<?php
/*------------------------------------------------------------------------
# Alias as body class
# ------------------------------------------------------------------------
# version 1.0.0
# author Kind of Useful
# copyright Copyright (C) 2019 Kind of Useful. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: https://www.kindofuseful.com
# Technical Support: info@kindofuseful.com
-------------------------------------------------------------------------*/

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

class plgSystemKouAliasAsBodyClass extends JPlugin
{	
	function __construct( $subject, $params )
	{
		parent::__construct($subject, $params);

	}

	function onBeforeRender()	{

		$app 		= JFactory::getApplication();

		// If we are on admin don't process.
		if (!$app->isClient('site'))
		{
			return;
		}

				
		$menu      	= $app->getMenu();
		$active    	= $menu->getActive();
		$alias     	= ucfirst($active->alias);		
		$document 	= JFactory::getDocument();

		if (!empty($prefix))
		{
			$alias = $prefix . $alias;
		}
		

		$javascript='
			document.addEventListener("DOMContentLoaded", function() {
				document.body.className += ("' . ' ' . $alias  . '");
			});
		';		
		$document->addScriptDeclaration($javascript);
	}
}