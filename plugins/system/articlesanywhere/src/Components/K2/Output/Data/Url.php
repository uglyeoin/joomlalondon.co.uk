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

use JLoader;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Router\Route as JRoute;
use Joomla\CMS\Uri\Uri as JUri;
use K2HelperPermissions;
use K2HelperRoute;

class Url extends \RegularLabs\Plugin\System\ArticlesAnywhere\Output\Data\Url
{
	public function getArticleUrl()
	{
		$url = $this->item->get('url');

		if ( ! is_null($url))
		{
			return $url;
		}

		$id = $this->item->getId();
		if ( ! $id)
		{
			return false;
		}

		require_once JPATH_SITE . '/components/com_k2/helpers/route.php';

		$this->item->set('url',
			K2HelperRoute::getItemRoute(
				$id . ':' . $this->item->get('alias'),
				$this->item->get('category-id') . ':' . $this->item->get('category-alias')
			)
		);

		if ( ! $this->item->hasAccess())
		{
			$this->item->set('url', $this->getRestrictedUrl($this->item->get('url')));
		}

		return $this->item->get('url');
	}

	public function getCategoryUrl()
	{
		return '';
	}

	public function getEditLink($attributes)
	{
		if ( ! $url = $this->getEditUrl())
		{
			return $url;
		}

		$text = ! empty($attributes->text) ? JText::_($attributes->text) : '';

		if (empty($attributes->text))
		{
			$state = $this->item->get('state', $this->item->get('published', 0));
			$text  = '<span class="icon-' . ($state ? 'edit' : 'eye-close') . '"></span>&nbsp;' . JText::_('JGLOBAL_EDIT');
		}

		return '<a href="' . $url . '">' . $text . '</a>';
	}

	public function getEditUrl()
	{
		if ( ! is_null($this->item->get('editurl')))
		{
			return $this->item->get('editurl');
		}

		if (is_null($this->item->getId()) || ! $this->item->getId())
		{
			return false;
		}

		$this->item->set('editurl', '');

		if ( ! $this->canEdit())
		{
			return '';
		}

		$uri = JUri::getInstance();

		$this->item->set(
			'editurl',
			JRoute::_('index.php?option=com_content&task=article.edit&a_id=' . $this->item->getId() . '&return=' . base64_encode($uri))
		);

		return $this->item->get('editurl');
	}

	protected function getCategoryId()
	{
		$catid = $this->item->get('catid');

		if ($catid)
		{
			return $catid;
		}

		$input = JFactory::getApplication()->input;

		// Get id from category view
		if ($input->get('option') == 'com_content' && $input->get('view', 'category') == 'category')
		{
			return $input->get('id');
		}

		return null;
	}

	protected function canEdit()
	{
		$user = JFactory::getUser();

		if ($user->get('guest'))
		{
			return false;
		}

		JLoader::register('K2HelperPermissions', JPATH_SITE . '/components/com_k2/helpers/permissions.php');

		if (JFactory::getApplication()->input->get('option') != 'com_k2')
		{
			K2HelperPermissions::setPermissions();
		}

		return K2HelperPermissions::canEditItem(
			$this->item->get('created_by'),
			$this->item->get('category-id')
		);
	}
}
