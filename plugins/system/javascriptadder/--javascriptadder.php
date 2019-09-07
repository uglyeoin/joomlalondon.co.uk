<?php
/*------------------------------------------------------------------------
# JAVASCRIPT_ADDER - Add Custom Javascript
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

class plgSystemJavascriptAdder extends JPlugin
{	
	function __construct( $subject, $params )
	{
		parent::__construct($subject, $params);

	}
	
	function onBeforeCompileHead()
	{
		$app = JFactory::getApplication();
		$document = JFactory::getDocument();

		$subform_items		= $this->params->get('javascripts');

		foreach ($subform_items as $item) {
			$inline_or_file = $item->inline_or_file;
			$file = $item->file;
			$inline = $item->inline;

			if ($inline_or_file == 0 && !empty($inline)) {
				$js = $inline;
				$document->addScriptDeclaration($js);				
			}
			elseif ($inline_or_file == 1 && !empty($file)) {
				$js = $file;
				$document->addScript($js);
			}
			else {
				$js = "console.log('There was an issue with JAVASCRIPT_ADDER at the if statement checking if inline or file was empty, perhaps you did not specify a file or any inline content?')"; 
				$document->addScriptDeclaration($js);				
				return;
			}
		}
	}
}
?>
<script>
var myFunction = document.body.className += ("m-MENU");
document.addEventListener('"DOMContentLoaded", myFunction');
// add event listener expects two arguments but only one was supplied.
</script>
