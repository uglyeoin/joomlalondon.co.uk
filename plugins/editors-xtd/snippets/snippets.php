<?php
/**
 * @package         Snippets
 * @version         6.5.4PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2019 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;


use RegularLabs\Plugin\System\Snippets\Items as SNP_Items;

if ( ! is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php'))
{
	return;
}

if ( ! is_file(JPATH_PLUGINS . '/system/snippets/vendor/autoload.php'))
{
	return;
}

require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';
require_once JPATH_PLUGINS . '/system/snippets/vendor/autoload.php';

/**
 ** Plugin that places the button
 */
class PlgButtonSnippets
	extends \RegularLabs\Library\EditorButtonPlugin
{
	var $main_type         = 'component';
	var $check_installed   = ['component', 'plugin'];
	var $require_core_auth = false;

	public function extraChecks($params)
	{
		$items = SNP_Items::getItems();

		if (empty($items))
		{
			return false;
		}

		return true;
	}
}
