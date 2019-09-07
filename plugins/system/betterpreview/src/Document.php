<?php
/**
 * @package         Better Preview
 * @version         6.2.2PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2019 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\BetterPreview;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text as JText;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\StringHelper as RL_String;

/**
 ** Plugin that places the button
 */
class Document
{
	public static function loadScriptsAndCSS()
	{
		RL_Document::script('betterpreview/script.min.js', '6.2.2.p');
		RL_Document::style('betterpreview/style.min.css', '6.2.2.p');


		$params = Params::get();

		if ($params->show_url_details)
		{

			$script = "
				var betterpreview_texts = {
					'url': '" . addslashes(RL_String::html_entity_decoder(JText::_('RL_URL'))) . "',
					'nonsef': '" . addslashes(RL_String::html_entity_decoder(JText::_('BP_NON_SEF_URL'))) . "'
				};
			";
			RL_Document::scriptDeclaration($script);
		}
	}
}
