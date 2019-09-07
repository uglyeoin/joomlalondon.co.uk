<?php
/**
 * @package         Modals
 * @version         11.5.2PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2019 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\Modals;

defined('_JEXEC') or die;


use JFolder;
use RegularLabs\Library\ArrayHelper as RL_Array;
use RegularLabs\Library\File as RL_File;
use RegularLabs\Library\Image as RL_Image;
use RegularLabs\Library\RegEx as RL_RegEx;

class Gallery
{
	public static function buildGallery($attributes, $tag_attributes, $content)
	{
		$folder = RL_File::trimFolder($tag_attributes['gallery']);

		jimport('joomla.filesystem.folder');
		if ( ! JFolder::exists(JPATH_SITE . '/' . $folder))
		{
			return '<a href="#">';
		}

		unset($tag_attributes['gallery']);
		unset($tag_attributes['inline']);

		$tag_attributes['group'] = uniqid('gallery_') . rand(1000, 9999);

		$params = Params::get();

		$settings = (object) [];

		$settings->createthumbnails = isset($tag_attributes['create-thumbnails']) ? $tag_attributes['create-thumbnails'] : $params->create_thumbnails;
		unset($tag_attributes['create-thumbnails']);

		$settings->{'thumbnail-folder'} = isset($tag_attributes['thumbnail-folder']) ? $tag_attributes['thumbnail-folder'] : $params->thumbnail_folder;
		unset($tag_attributes['thumbnail-folder']);

		$settings->{'thumbnail-suffix'} = isset($tag_attributes['thumbnail-suffix'])
			? $tag_attributes['thumbnail-suffix']
			: $params->thumbnail_legacy ? $params->thumbnail_suffix : '';
		unset($tag_attributes['thumbnail-suffix']);

		if ( ! empty($tag_attributes['thumbnail-width']) || ! empty($tag_attributes['thumbnail-height']))
		{
			$settings->{'thumbnail-width'}  = ! empty($tag_attributes['thumbnail-width']) ? $tag_attributes['thumbnail-width'] : 0;
			$settings->{'thumbnail-height'} = ! empty($tag_attributes['thumbnail-height']) ? $tag_attributes['thumbnail-height'] : 0;
		}
		unset($tag_attributes['thumbnail-width']);
		unset($tag_attributes['thumbnail-height']);

		$settings->{'thumbnail-quality'} = isset($tag_attributes['thumbnail-quality']) ? $tag_attributes['thumbnail-quality'] : $params->thumbnail_quality;
		unset($tag_attributes['thumbnail-quality']);

		$settings->separator = isset($tag_attributes['separator']) ? $tag_attributes['separator'] : str_replace('{none}', '', $params->gallery_separator);
		unset($tag_attributes['separator']);

		$settings->filter = isset($tag_attributes['filter']) ? $tag_attributes['filter'] : $params->gallery_filter;
		unset($tag_attributes['filter']);

		$settings->{'auto-titles'} = isset($tag_attributes['auto_titles']) ? $tag_attributes['auto_titles'] : $params->auto_titles;
		unset($tag_attributes['auto_titles']);

		$settings->{'title-case'} = isset($tag_attributes['title_case']) ? $tag_attributes['title_case'] : $params->title_case;
		unset($tag_attributes['title_case']);

		$settings->images = isset($tag_attributes['images']) ? $tag_attributes['images'] : '';
		unset($tag_attributes['images']);

		$settings->thumbnail = isset($tag_attributes['thumbnail']) ? $tag_attributes['thumbnail'] : '';
		unset($tag_attributes['thumbnail']);

		// Support for old show / showall attributes
		$showall = isset($tag_attributes['showall']) ? $tag_attributes['showall'] : $params->gallery_showall;
		unset($tag_attributes['showall']);

		$show = isset($tag_attributes['show']) ? $tag_attributes['show'] : ($showall ? 'all' : '');
		unset($tag_attributes['show']);

		if (empty($settings->thumbnail) && ! empty($show))
		{
			$settings->thumbnail = $show;
		}

		$settings->first = isset($tag_attributes['first']) ? $tag_attributes['first'] : '';
		unset($tag_attributes['first']);

		if (isset($tag_attributes['title']))
		{
			$settings->title = $tag_attributes['title'];
			unset($tag_attributes['title']);
		}

		if (isset($tag_attributes['alt']))
		{
			$settings->title = $tag_attributes['alt'];
			unset($tag_attributes['alt']);
		}

		if (isset($tag_attributes['description']))
		{
			$settings->title = $tag_attributes['description'];
			unset($tag_attributes['description']);
		}

		$images            = self::getGalleryImageList($folder, $settings);
		$thumbnail_ids     = self::getThumbnailsIds($settings->thumbnail, $images);
		$settings->firstid = self::getFirstID($settings->first, $images);

		$html = [];

		foreach ($images as $count => $image)
		{
			$show = in_array($count, $thumbnail_ids);

			$image_attributes = clone $attributes;

			if ( ! $show)
			{
				// Add hidden class to images that don't show the thumbnail
				$attributes->class .= ' modal_link_hidden';
				$attributes->id    = '';
			}

			$html[] = self::getGalleryImageLink($image, $image_attributes, $tag_attributes, $content, $settings, $count, $show);
		}

//		$shown  = [];
//		$hidden = [];
//
//		foreach ($thumbnail_ids as $thumbnail_id)
//		{
//			$shown[] = self::getGalleryImageLink($images[$thumbnail_id], $attributes, $tag_attributes, $content, $settings, $thumbnail_id, true);
//		}
//
//		// Add hidden class to other images if not show all
//		$attributes->class .= ' modal_link_hidden';
//		$attributes->id    = '';
//
//		foreach ($images as $count => $image)
//		{
//			// Skip shown images
//			if (in_array($count, $thumbnail_ids))
//			{
//				continue;
//			}
//
//			$hidden[] = self::getGalleryImageLink($image, $attributes, $tag_attributes, $content, $settings, $count, false);
//		}
//
//		$html = array_merge($shown, $hidden);

		return implode('</a>' . $settings->separator, $html);
	}

	private static function filterImageList($show, &$images)
	{
		// Default to all images in natural order
		if (empty($images) || empty($show) || $show == 'all')
		{
			return;
		}

		// Randomize image order
		if ($show == 'random')
		{
			shuffle($images);

			return;
		}

		// Convert single number to a range starting at 1
		if (is_numeric($show))
		{
			$show = '1-' . $show;
		}

		// Get a range of images numbers
		if (RL_RegEx::match('^([0-9]+)-([0-9]+)$', $show, $show_range))
		{
			$range = [];

			for ($i = $show_range[1]; $i <= $show_range[2]; $i++)
			{
				$range[] = $images[$i - 1];
			}

			$images = $range;

			return;
		}

		// Find images from a list of names or numbers
		// Also works for single values
		$show = RL_Array::toArray($show);

		$range = [];

		foreach ($show as $name)
		{
			foreach ($images as $id => $image)
			{
				/* @var Image $image */
				if (in_array(strtolower($name), [
					$id + 1,
					$image->getFileName(true),
					$image->getBaseName(true),
				]))
				{
					$range[] = $image;
					unset($images[$id]);
				}
			}
		}

		$images = $range;
	}

	private static function getThumbnailsIds($show, $images)
	{
		// Default to first image if stuff is empty
		if (empty($images) || empty($show) || $show == 'first')
		{
			return [0];
		}

		// Add all images to the list of thumbnails
		if ($show == 'all')
		{
			return array_keys($images);
		}

		// Find a random image (number)
		if ($show == 'random')
		{
			return [array_rand($images, 1)];
		}

		// Convert single number to a range starting at 1
		if (is_numeric($show))
		{
			$show = '1-' . $show;
		}

		// Get a range of images numbers
		if (RL_RegEx::match('^([0-9]+)-([0-9]+)$', $show, $show_range))
		{
			$range = [];

			for ($i = $show_range[1]; $i <= $show_range[2]; $i++)
			{
				$range[] = $i - 1;
			}

			// Default to first image if nothing is found
			return ! empty($range) ? $range : [0];
		}

		// Find images from a list of names or numbers
		// Also works for single values
		$show = RL_Array::toArray($show);

		$range = [];

		foreach ($show as $name)
		{
			foreach ($images as $id => $image)
			{
				/* @var Image $image */
				if (in_array($name, [
					$id + 1,
					$image->getFileName(true),
					$image->getBaseName(true),
				]))
				{
					$range[] = $id;
				}
			}
		}

		// Default to first image if nothing is found
		return ! empty($range) ? $range : [0];
	}

	private static function getFirstID($first, $images)
	{
		// Default to first image if stuff is empty
		if (empty($images) || empty($first))
		{
			return 0;
		}

		// Look up image by number
		if (is_numeric($first))
		{
			return isset($images[$first - 1]) ? $first - 1 : 0;
		}

		// Find a random image (number)
		if ($first == 'random')
		{
			return array_rand($images, 1);
		}

		// Find image by name or number
		foreach ($images as $id => $image)
		{
			/* @var Image $image */
			if ($first == $image->getFileName(true)
				|| $first == $image->getBaseName(true))
			{
				return $id;
			}
		}

		// Default to first image
		return 0;
	}

	private static function getGalleryImageList($folder, &$settings)
	{
		$folder = RL_File::trimFolder($folder);
		$filter = $settings->filter;

		if (RL_RegEx::match('(.*?\()([^\)]*)(\).*?)', $settings->filter, $match))
		{
			$filter = $match[1] . $match[2] . '|' . strtoupper($match[2]) . $match[3];
		}

		$files = JFolder::files(JPATH_SITE . '/' . $folder, $filter);

		$images = [];
		foreach ($files as $file)
		{
			if ($image = RL_Image::isResized($folder . '/' . $file, $settings->{'thumbnail-folder'}, $settings->{'thumbnail-suffix'}))
			{
				continue;
			}

			$images[] = new Image($folder . '/' . $file, $settings);
		}

		self::filterImageList($settings->images, $images);

		return $images;
	}

	private static function getGalleryImageLink($image, &$attributes, &$data, $content, $settings, &$count, $show = true)
	{
		$attributes->href = $image->getHref();
		$image_data       = $data;

		if ($count != $settings->firstid)
		{
			unset($image_data['open']);
		}

		$image_data['title']       = $image->attributes->title;
		$image_data['alt']         = $image->attributes->alt;
		$image_data['description'] = $image->attributes->description;

		$link = Link::build($attributes, $image_data);

		if ($show)
		{
			$link = str_replace(' modal_link_hidden', '', $link);
		}

		if ($show && $content == '')
		{
			return $link . $image->thumbnail->getHtmlTag();
		}

		if ($count != $settings->firstid)
		{
			return $link;
		}

		$link = str_replace(' modal_link_hidden', '', $link);

		return $link . $content;
	}
}
