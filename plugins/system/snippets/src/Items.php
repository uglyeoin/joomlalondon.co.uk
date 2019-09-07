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

namespace RegularLabs\Plugin\System\Snippets;

defined('_JEXEC') or die;

use RegularLabs\Library\StringHelper as RL_String;
use SnippetsModelList;

class Items
{
	static $items = null;

	public static function get($id = '')
	{
		if (empty($id))
		{
			return null;
		}

		$items = self::getItems();

		if (isset($items[$id]))
		{
			return $items[$id];
		}

		$id = RL_String::html_entity_decoder($id);

		if (isset($items[$id]))
		{
			return $items[$id];
		}

		return null;
	}

	public static function getItems()
	{
		if ( ! is_null(self::$items))
		{
			return self::$items;
		}

		require_once JPATH_ADMINISTRATOR . '/components/com_snippets/models/list.php';

		$list = new SnippetsModelList;

		self::$items = $list->getItems(1);

		return self::$items;
	}
}
