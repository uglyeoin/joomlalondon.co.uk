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

namespace RegularLabs\Plugin\System\BetterPreview\Component\K2\Item;

defined('_JEXEC') or die;

use RegularLabs\Plugin\System\BetterPreview\Component\Button as Main_Button;
use RegularLabs\Plugin\System\BetterPreview\Component\Menu;

class Button extends Main_Button
{
	function getExtraJavaScript($text)
	{
		return '
				isjform = 0;
				text = text.split(\'<hr id="system-readmore">\');
				introtext = text[0];
				fulltext =  text[1] == undefined ? "" : text[1];
				text = (introtext + " " + fulltext).trim();
				overrides = {
						text: text,
						introtext: introtext,
						fulltext: fulltext,
					};
			';
	}

	function getURL($name)
	{
		if ( ! $item = Helper::getK2Item())
		{
			return false;
		}

		Menu::setItemId($item);

		return $item->url;
	}
}
