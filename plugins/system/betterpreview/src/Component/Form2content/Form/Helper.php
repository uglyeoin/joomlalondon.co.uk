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

namespace RegularLabs\Plugin\System\BetterPreview\Component\Form2content\Form;

defined('_JEXEC') or die;

use ContentHelperRoute;
use Joomla\CMS\Factory as JFactory;
use RegularLabs\Plugin\System\BetterPreview\Component\Helper as Main_Helper;
use RegularLabs\Plugin\System\BetterPreview\Component\Menu;

if ( ! class_exists('ContentHelperRoute'))
{
	require_once JPATH_SITE . '/components/com_content/helpers/route.php';
}

class Helper extends Main_Helper
{
	public static function getArticle()
	{
		if (JFactory::getApplication()->input->get('layout', 'edit') != 'edit'
			|| ! JFactory::getApplication()->input->get('id')
		)
		{
			return false;
		}

		$db = JFactory::getDbo();

		$query = $db->getQuery(true)
			->select('c.reference_id')
			->from('#__f2c_form AS c')
			->where('c.id = ' . (int) JFactory::getApplication()->input->get('id'));
		$db->setQuery($query);
		$article_id = $db->loadResult();

		$item = self::getItem(
			$article_id,
			'content',
			['name' => 'title', 'published' => 'state', 'language' => 'language', 'parent' => 'catid'],
			['type' => 'RL_ARTICLE']
		);

		$item->url = ContentHelperRoute::getArticleRoute($item->id, $item->parent, $item->language);

		Menu::setItemId($item);

		return $item;
	}

	public static function getArticleParents($item)
	{
		if (empty($item)
			|| JFactory::getApplication()->input->get('layout', 'edit') != 'edit'
			|| ! JFactory::getApplication()->input->get('id')
		)
		{
			return false;
		}

		$parents = self::getParents(
			$item,
			'categories',
			['name' => 'title', 'parent' => 'parent_id', 'language' => 'language'],
			['type' => 'JCATEGORY'],
			1
		);

		foreach ($parents as &$parent)
		{
			$parent->url = ContentHelperRoute::getCategoryRoute($parent->id, $item->language);
		}

		return $parents;
	}
}
