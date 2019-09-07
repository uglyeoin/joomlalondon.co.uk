<?php
/**
 * @package         CDN for Joomla!
 * @version         6.1.3PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2019 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\CDNforJoomla;

defined('_JEXEC') or die;

use RegularLabs\Library\Protect as RL_Protect;

class Protect
{
	static $name = 'CDNforJoomla';

	public static function _(&$string)
	{
		RL_Protect::protectHtmlCommentTags($string);
		RL_Protect::protectFields($string);
		RL_Protect::protectSourcerer($string);
		RL_Protect::protectByRegex($string, '\{nocdn\}.*?\{/nocdn\}');
	}
}
