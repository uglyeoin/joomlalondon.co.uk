<?php
/**
 * @package         Tooltips
 * @version         7.4.1PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2019 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\Tabs\EditorButton;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\RegEx as RL_RegEx;

class Popup
	extends \RegularLabs\Library\EditorButtonPopup
{
	var $require_core_auth = false;

	public function loadScripts()
	{
		// Tag character start and end
		list($tag_start, $tag_end) = explode('.', $this->params->tag_characters);

		$editor = JFactory::getApplication()->input->getString('name', 'text');
		// Remove any dangerous character to prevent cross site scripting
		$editor = RL_RegEx::replace('[\'\";\s]', '', $editor);

		$script = "
			var tooltips_tag = '" . RL_RegEx::replace('[^a-z0-9-_]', '', $this->params->tag) . "';
			var tooltips_tag_characters = ['" . $tag_start . "', '" . $tag_end . "'];
			var tooltips_editorname = '" . $editor . "';
			var tooltips_text_placeholder = '" . JText::_('TT_TEXT', true) . "';
			var tooltips_error_empty_image = '" . JText::_('TT_ERROR_EMPTY_IMAGE', true) . "';
			var tooltips_error_empty_text = '" . JText::_('TT_ERROR_EMPTY_TEXT', true) . "';
		";
		RL_Document::scriptDeclaration($script);

		RL_Document::script('tooltips/popup.min.js', '7.4.1.p');
	}
}

$popup = new Popup('tooltips');
$popup->render();
