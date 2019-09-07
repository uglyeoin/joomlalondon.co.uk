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

namespace RegularLabs\Plugin\System\ArticlesAnywhere\Output\Data;

defined('_JEXEC') or die;

use ContentHelperRoute;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Router\Route as JRoute;
use Joomla\CMS\Uri\Uri as JUri;
use RegularLabs\Library\HtmlTag as RL_HtmlTag;

class Url extends Data
{
	public function get($key, $attributes)
	{
		switch ($key)
		{
			case 'edit':
				return $this->getEditTag($attributes);

			case 'edit-link':
				return $this->getEditLink($attributes);

			case 'edit-url':
				return $this->getEditUrl();

			case 'category-link':
				return $this->getCategoryLink($attributes);

			case 'category-url':
				return $this->getCategoryUrl();

			case 'category-sefurl':
				return JRoute::_($this->getCategoryUrl());

			case 'link':
				return $this->getArticleLink($attributes);

			case 'sefurl':
				return JRoute::_($this->getArticleUrl());

			default:
			case 'url':
			case 'nonsefurl':
				return $this->getArticleUrl();
		}
	}

	public function getLink($url, $attributes = [])
	{
		$url = $url ?: '#';

		$attributes = array_merge(
			['href' => $url],
			(array) $attributes
		);

		return '<a ' . RL_HtmlTag::flattenAttributes($attributes) . '>';
	}

	public function getArticleLink($attributes)
	{
		return $this->getLink($this->getArticleUrl(), $attributes);
	}

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

		if ( ! class_exists('ContentHelperRoute'))
		{
			require_once JPATH_SITE . '/components/com_content/helpers/route.php';
		}

		$this->item->set('url', ContentHelperRoute::getArticleRoute($id, $this->item->get('catid'), $this->item->get('language')));

		if ( ! $this->item->hasAccess())
		{
			$this->item->set('url', $this->getRestrictedUrl($this->item->get('url')));
		}

		return $this->item->get('url');
	}


	public function getCategoryLink($attributes)
	{
		return $this->getLink($this->getCategoryUrl(), $attributes);
	}

	public function getCategoryUrl()
	{
		$category_url = $this->item->get('category-url');

		if ( ! is_null($category_url))
		{
			return $category_url;
		}

		$catid = $this->getCategoryId();

		if (is_null($catid))
		{
			return false;
		}

		if ( ! class_exists('ContentHelperRoute'))
		{
			require_once JPATH_SITE . '/components/com_content/helpers/route.php';
		}

		$this->item->set('category-url', ContentHelperRoute::getCategoryRoute($catid, $this->item->get('language')));

		if ( ! $this->item->hasAccess())
		{
			$this->item->set('category-url', $this->getRestrictedUrl($this->item->get('category-url')));
		}

		return $this->item->get('category-url');
	}

	public function getEditTag($attributes)
	{
		if ( ! $url = $this->getEditUrl())
		{
			return $url;
		}

		$attributes->class = isset($attributes->class) ? $attributes->class : 'btn btn-default';

		$state = $this->item->get('state', $this->item->get('published', 0));
		$icon  = '<span class="icon-' . ($state ? 'edit' : 'eye-close') . '"></span>';
		$text  = ! empty($attributes->text) ? JText::_($attributes->text) : JText::_('JGLOBAL_EDIT');

		return $this->getLink($url, $attributes)
			. $icon . '&nbsp;' . $text
			. '</a>';
	}

	public function getEditLink($attributes)
	{
		if ( ! $url = $this->getEditUrl())
		{
			return $url;
		}

		return $this->getLink($url, $attributes);
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
			'index.php?option=com_content&task=article.edit&a_id=' . $this->item->getId() . '&return=' . base64_encode($uri)
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

	protected function getRestrictedUrl($url)
	{
		$menu   = JFactory::getApplication()->getMenu();
		$active = $menu->getActive();
		$itemId = $active ? $active->id : 0;
		$link   = new JUri(JRoute::_('index.php?option=com_users&view=login&Itemid=' . $itemId, false));

		$link->setVar('return', base64_encode(JRoute::_($url, false)));

		return (string) $link;
	}

	protected function canEdit()
	{
		$user = JFactory::getUser();
		if ($user->get('guest'))
		{
			return false;
		}

		$userId = $user->get('id');
		$asset  = 'com_content.article.' . $this->item->getId();

		// Check general edit permission first.
		if ($user->authorise('core.edit', $asset))
		{
			return true;
		}

		// Now check if edit.own is available.
		if (empty($userId) || ! $user->authorise('core.edit.own', $asset))
		{
			return false;
		}

		// Check for a valid user and that they are the owner.
		if ($userId != $this->item->get('created_by'))
		{
			return false;
		}

		return true;
	}
}
