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


use Joomla\CMS\Access\Access as JAccess;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Layout\FileLayout as JLayoutFile;
use Joomla\CMS\Router\Route as JRoute;
use RegularLabs\Plugin\System\ArticlesAnywhere\Collection\Item;
use RegularLabs\Plugin\System\ArticlesAnywhere\Config;
use RegularLabs\Plugin\System\ArticlesAnywhere\Output\Values;
use TagsHelperRoute;

class Tags extends Data
{
	private $access_levels;

	public function __construct(Config $config, Item $item, Values $values)
	{
		parent::__construct($config, $item, $values);

		$this->access_levels = JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'));
	}

	public function get($key, $attributes)
	{
		$tags = $this->item->getTags();

		if (isset($attributes->output)
			&& in_array($attributes->output, ['value', 'values', 'raw']))
		{
			$array = [];

			foreach ($tags as $tag)
			{
				if ( ! in_array($tag->access, $this->access_levels))
				{
					continue;
				}

				$array[] = $tag->title;
			}

			return $array;
		}

		if (
			JFactory::getApplication()->input->get('option') == 'com_finder'
			&& JFactory::getApplication()->input->get('format') == 'json'
		)
		{
			// Force normal layout for finder indexing, as the TagsHelperRoute causes errors
			$attributes->clean = true;
		}

		if ( ! empty($attributes->strip))
		{
			$separator = isset($attributes->separator) ? $attributes->separator : (isset($attributes->delimiter) ? $attributes->delimiter : ', ');

			return $this->processTagTagsStripped($tags, $separator);
		}

		if ( ! empty($attributes->clean))
		{
			$separator = isset($attributes->separator) ? $attributes->separator : (isset($attributes->delimiter) ? $attributes->delimiter : ' ');

			return $this->processTagTagsClean($tags, $separator);
		}

		return $this->processTagTagsLayout($tags);
	}

	private function processTagTagsLayout($tags)
	{
		$layout = new JLayoutFile('joomla.content.tags');

		return $layout->render($tags);
	}

	private function processTagTagsStripped($tags, $separator = ', ')
	{
		$html = [];

		foreach ($tags as $tag)
		{
			if ( ! in_array($tag->access, $this->access_levels))
			{
				continue;
			}

			$html[] = htmlspecialchars($tag->title, ENT_COMPAT, 'UTF-8');
		}

		return implode($separator, $html);
	}

	private function processTagTagsClean($tags, $separator = ' ')
	{
		require_once JPATH_ROOT . '/components/com_tags/helpers/route.php';

		$html = [];

		foreach ($tags as $tag)
		{
			if ( ! in_array($tag->access, $this->access_levels))
			{
				continue;
			}

			$html[] = '<span class="tag-' . $tag->tag_id . '" itemprop="keywords">'
				. '<a href = "' . JRoute::_(TagsHelperRoute::getTagRoute($tag->tag_id . '-' . $tag->alias)) . '" class="tag_link">'
				. htmlspecialchars($tag->title, ENT_COMPAT, 'UTF-8')
				. '</a>'
				. '</span>';
		}

		return '<span class="tags">' . implode($separator, $html) . '</span>';
	}

}
