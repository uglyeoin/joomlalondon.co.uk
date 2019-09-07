<?php
/**
 * @package         Articles Anywhere
 * @version         9.3.5PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2019 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\ArticlesAnywhere\Components\K2\Output\Data;

defined('_JEXEC') or die;

use RegularLabs\Plugin\System\ArticlesAnywhere\Factory;
use RegularLabs\Plugin\System\ArticlesAnywhere\Output\Data\Data;

class Layout extends Data
{
	public function get($key, $attributes)
	{
		// K2 layouts are too complicated and spaghetti-code. So just output a simple title and text
		$text = Factory::getOutput('Text', $this->config, $this->item);

		return
			'<h2 class="itemTitle">'
			. $this->item->get('title')
			. '</h2>'
			. '<div class="itemBody">'
			. $text->get('text', $attributes)
			. '</div>';
	}
}
