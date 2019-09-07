<?php
/**
 * @package         Articles Anywhere
 * @version         6.3.0PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2017 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\ArticlesAnywhere;

defined('_JEXEC') or die;


use JComponentHelper;
use JFactory;
use JFile;
use JLoader;
use JRoute;
use JTable;
use JText;
use JUri;
use K2HelperPermissions;
use K2HelperRoute;
use K2ModelItem;

class DataTagsK2 extends DataTags
{
	public function getArticleUrl()
	{
		$article = Article::get();

		if (isset($article->url))
		{
			return $article->url;
		}

		if ( ! isset($article->id))
		{
			return false;
		}

		if ( ! isset($article->category))
		{
			JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_k2/tables');
			$category = JTable::getInstance('K2Category', 'Table');
			$category->load($article->catid);
			$article->category = $category;
		}

		require_once JPATH_SITE . '/components/com_k2/helpers/route.php';
		$article->url = K2HelperRoute::getItemRoute($article->id . ':' . $article->alias, $article->catid . ':' . $article->category->alias);

		if (empty($article->has_access))
		{
			$article->url = $this->getRestrictedUrl($article->url);
		}

		return $article->url;
	}

	public function processTagDatabase($tag, $return_empty = false)
	{
		// Get data from data object, even, uneven, first, last
		if (is_bool(Numbers::get($tag->type)))
		{
			return Numbers::get($tag->type) ? 'true' : 'false';
		}

		// Get data from db columns
		$string = $this->getTagFromDatabase($tag);

		if ($string === false)
		{
			return $return_empty ? '' : false;
		}

		// Convert string if it is a date
		$string = $this->convertDateToString($string, isset($tag->format) ? $tag->format : '', $tag->type);

		return $string;
	}

	private function getTagFromDatabase($tag)
	{
		$article = Article::get();

		if (empty($tag->label) && isset($article->{$tag->type}))
		{
			return $article->{$tag->type};
		}

		return $this->getTagFromExtraField($tag);
	}

	private function getTagFromExtraField($tag)
	{
		$article = Article::get();

		$fielddata = json_decode($article->extra_fields);
		$string    = $this->getExtraFieldOutput($tag, $fielddata, $article->catid);

		if ($string === false)
		{
			return false;
		}

		return $string;
	}

	public function canEdit()
	{
		$params  = Params::get();
		$article = Article::get();

		JLoader::register('K2HelperPermissions', JPATH_SITE . '/components/com_k2/helpers/permissions.php');

		if ($params->option != 'com_k2')
		{
			K2HelperPermissions::setPermissions();
		}

		return K2HelperPermissions::canEditItem($article->created_by, $article->catid);
	}

	public function getArticleEditUrl()
	{
		$article = Article::get();

		if (isset($article->editurl))
		{
			return $article->editurl;
		}

		if ( ! isset($article->id))
		{
			return false;
		}

		$article->editurl = '';

		if ( ! $this->canEdit())
		{
			return false;
		}

		$uri = JUri::getInstance();

		$article->editurl = JRoute::_('index.php?option=com_k2&view=item&task=edit&cid=' . $article->id . '&return=' . base64_encode($uri));

		return $article->editurl;
	}

	public function getValueFromData($key, $default = null)
	{
		$value = parent::getValueFromData($key);

		if ( ! is_null($value))
		{
			return $value;
		}

		$article = Article::get();

		$fielddata = json_decode($article->extra_fields);
		$extra     = $this->getExtraFieldOutput($key, $fielddata, $article->catid);

		if ($extra)
		{
			return $extra;
		}

		return $default;
	}

	/*
	 * Retrieve data from k2 extra fields
	 */
	private function getExtraFieldOutput($tag, $fielddata, $catid)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->clear()
			->select('c.extraFieldsGroup')
			->from('#__k2_categories as c')
			->where('c.id = ' . (int) $catid);
		$db->setQuery($query);
		$extragroup = $db->loadResult();

		if (empty($extragroup))
		{
			return false;
		}

		$type = isset($tag->type) ? $tag->type : $tag;

		$query->clear()
			->select('e.*')
			->from('#__k2_extra_fields as e')
			->where('e.group = ' . (int) $extragroup)
			->where('e.published = 1');

		$where = 'e.name = ' . $db->quote($type);
		if (substr($type, 0, 6) == 'extra-' && is_numeric(substr($type, 6)))
		{
			$where = '(' . $where . ' OR e.id = ' . (int) substr($type, 6) . ')';
		}
		$query->where($where);

		$db->setQuery($query);
		$extrafield = $db->loadObject();

		if (empty($extrafield))
		{
			return false;
		}

		$show_label = isset($tag->label) ? $tag->label : false;

		if ($show_label === 'only')
		{
			return $extrafield->name;
		}

		$value = $this->getExtraFieldValue($extrafield, $tag, $fielddata);

		if ( ! $show_label)
		{
			return $value;
		}

		$format = isset($tag->format) ? $tag->format : '%s: %s';

		return sprintf($format, $extrafield->name, $value);
	}

	private function getExtraFieldValue($extrafield, $tag, $fielddata)
	{
		$value = false;

		foreach ($fielddata as $field)
		{
			if ($field->id != $extrafield->id)
			{
				continue;
			}

			if ($field->value == '')
			{
				continue;
			}

			$value = $field->value;

			if (in_array($extrafield->type, ['textfield', 'textarea', 'image', 'csv', 'date']))
			{
				return $value;
			}

			if ($extrafield->type == 'link' && is_array($field->value))
			{
				$link         = (object) [];
				$link->name   = isset($field->value['0']) ? $field->value['0'] : '';
				$link->value  = isset($field->value['1']) ? $field->value['1'] : '';
				$link->target = isset($field->value['2']) ? $field->value['2'] : '';

				return $this->getFieldLink($link);
			}

			break;
		}

		$defaultdata = json_decode($extrafield->value);

		if ($value == false && isset($defaultdata['0']))
		{
			switch ($extrafield->type)
			{
				case 'textfield':
				case 'textarea':
				case 'image':
				case 'csv':
					$value = $defaultdata['0']->value;
					break;
				case 'link':
					$value = $this->getFieldLink($defaultdata['0']);
					break;
				case 'multipleSelect':
					$value = '';
					break;
				default:
					$value = $defaultdata['0']->name;
					break;
			}
		}

		$values = [];
		foreach ($defaultdata as $defaultvalue)
		{
			if ( ! is_array($value))
			{
				$value = [$value];
			}

			foreach ($value as $val)
			{
				if ($val != $defaultvalue->value)
				{
					continue;
				}

				$values[] = $defaultvalue->name;
			}
		}

		if (empty($values))
		{
			return false;
		}

		return implode(', ', $values);
	}

	private function getFieldLink(&$field)
	{
		if ( ! $field->value || $field->value == 'http://')
		{
			return $field->name;
		}

		$params = JComponentHelper::getParams('com_k2');

		switch ($field->target)
		{
			case 'same':
			default:
				$attributes = '';
				break;

			case 'new':
				$attributes = 'target="_blank"';
				break;

			case 'popup':
				$attributes = 'class="classicPopup" rel="{x:' . $params->get('linkPopupWidth') . ',y:' . $params->get('linkPopupHeight') . '}"';
				break;

			case 'lightbox':
				$filename      = @basename($field->value);
				$extension     = JFile::getExt($filename);
				$imgExtensions = ['jpg', 'jpeg', 'gif', 'png'];
				$attributes    = 'class="modal"';
				if (empty($extension) || ! in_array($extension, $imgExtensions))
				{
					$attributes .= ' rel="{handler:\'iframe\',size:{x:' . $params->get('linkPopupWidth') . ',y:' . $params->get('linkPopupHeight') . '}}"';
				}
				break;
		}

		return '<a href="' . $field->value . '" ' . $attributes . '>' . $field->name . '</a>';
	}

	public function processTagTags($extra)
	{
		require_once JPATH_SITE . '/components/com_k2/helpers/route.php';

		$article = Article::get();
		$tags    = $this->getItemTags($article->id);
		foreach ($tags as &$tag)
		{
			$tag->link = JRoute::_(K2HelperRoute::getTagRoute($tag->name));
		}

		$extra = explode(':', $extra, 2);
		$clean = trim(array_shift($extra));

		$html = [];

		if ($clean != 'clean')
		{
			foreach ($tags as $tag)
			{
				if ( ! $tag->published)
				{
					continue;
				}

				$html[] = '<li><a href="' . $tag->link . '">' . htmlspecialchars($tag->name, ENT_COMPAT, 'UTF-8') . '</a></li>';
			}

			return
				'<div class="itemTagsBlock">'
				. '<span>' . JText::_('K2_TAGGED_UNDER') . '</span>'
				. '<ul class="itemTags">'
				. implode('', $html)
				. '</ul>'
				. '<div class="clr"></div>'
				. '</div>';
		}

		$separator = array_shift($extra);
		$separator = $separator != '' ? str_replace('separator=', '', $separator) : ' ';

		foreach ($tags as $tag)
		{
			if ( ! $tag->published)
			{
				continue;
			}

			$html[] = '<span class="tag-' . $tag->id . '" itemprop="keywords">'
				. '<a href = "' . $tag->link . '" class="tag_link">'
				. htmlspecialchars($tag->name, ENT_COMPAT, 'UTF-8')
				. '</a>'
				. '</span>';
		}

		return '<span class="tags">' . implode($separator, $html) . '</span>';
	}

	private function getItemTags($id)
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true)
			->select($db->quoteName('tags.name'))
			->from($db->quoteName('#__k2_tags', 'tags'))
			->join('LEFT', $db->quoteName('#__k2_tags_xref', 'xref')
				. ' ON ' . $db->quoteName('xref.tagID') . ' = ' . $db->quoteName('tags.id'))
			->where($db->quoteName('xref.itemID') . ' = ' . (int) $id)
			->where($db->quoteName('tags.published') . ' = 1')
			->order($db->quoteName('xref.id') . ' ASC');

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	public function hitArticle()
	{
		$params = Params::get();

		if ( ! $params->increase_hits_on_text)
		{
			return;
		}

		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_k2/tables');

		if ( ! class_exists('K2ModelItem'))
		{
			require_once JPATH_SITE . '/components/com_k2/models/item.php';
		}

		$model = new K2ModelItem;

		$model->hit(Article::get('id'));
	}
}
