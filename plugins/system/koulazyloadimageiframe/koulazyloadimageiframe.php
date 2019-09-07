<?php
/*------------------------------------------------------------------------
# Lazy Load Images & Iframes
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

class plgSystemKouLazyLoadImageIframe extends JPlugin
{	
	function onBeforeRender()
	{

		$app 			= JFactory::getApplication();

		// If we are on admin don't process.
		if (!$app->isClient('site'))
		{
			return;
		}

		$document 		= JFactory::getDocument();
		$menu      		= $app->getMenu();
		$active    		= $menu->getActive();
		$alias     		= ucfirst($active->alias);
		
		$document->addScript('https://cdnjs.cloudflare.com/ajax/libs/blazy/1.8.2/blazy.min.js');
		$document->addScriptDeclaration('
				// the code
				var bLazy = new Blazy({
				  success: function(){
					updateCounter();
					console.log("B Lazy success");
				  }				  
				});
				
				
				// not needed, only here to illustrate amount of loaded images
				var imageLoaded = 0;
				var eleCountLoadedImages = document.getElementById("loaded-images");
				
				function updateCounter() {
				  imageLoaded++;
				  eleCountLoadedImages.innerHTML = imageLoaded;
				}
		');
	}
}