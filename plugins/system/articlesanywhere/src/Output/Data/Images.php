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
use Joomla\CMS\Layout\LayoutHelper as JLayoutHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel as JModel;
use Joomla\CMS\Router\Route as JRoute;
use Joomla\CMS\Uri\Uri as JUri;
use RegularLabs\Library\ArrayHelper as RL_Array;
use RegularLabs\Library\File as RL_File;
use RegularLabs\Library\HtmlTag as RL_HtmlTag;
use RegularLabs\Library\Image as RL_Image;
use RegularLabs\Library\RegEx as RL_RegEx;
use RegularLabs\Library\StringHelper as RL_String;
use RegularLabs\Plugin\System\ArticlesAnywhere\Params;

class Images extends Data
{
	public function get($key, $attributes)
	{
		$regex = '^image(?<separator>[-_])(?<type>[0-9]+|random|intro|fulltext|category|count)([-_](?<data>[a-z][a-z0-9-_]*))?$';

		RL_RegEx::match($regex, $key, $image_tag);

		if (empty($image_tag))
		{
			return null;
		}

		$data = isset($image_tag['data']) ? $image_tag['data'] : '';

		if ($image_tag['separator'] == '_' && empty($data))
		{
			$data = 'url';
		}

		$data = str_replace(
			[
				'thumbnail',
			],
			[
				'thumb',
			],
			$data
		);
		$data = RL_RegEx::replace('-?(tag|img)$', '', $data);

		return $this->getByType($image_tag['type'], $data, $attributes);
	}

	protected function getByType($type, $data, $attributes)
	{
		switch ($type)
		{
			case 'intro':
				return $this->getIntroImage($data, $attributes);

			case 'fulltext':
				return $this->getFulltextImage($data, $attributes);

			case 'category':
				return $this->getCategoryImage($data, $attributes);

			case 'count':
				return $this->getContentImageCount();

			case 'random':
				$count = $this->getContentImageCount();

				return $this->getContentImage(rand(1, $count), $data, $attributes);

			default:


				return $this->getContentImage($type, $data, $attributes);

		}
	}

	public function getImageByUrl($url, &$attributes)
	{
		$image = ['url' => $url];

		$this->prepareImageUrl($image['url'], $attributes);

		$this->setResizedImage($image, $attributes);

		return $image;
	}

	protected function prepareImageUrl(&$url, $attributes)
	{
		if (isset($attributes->suffix))
		{
			$url = RL_RegEx::replace(
				'\.[a-z]*$',
				$attributes->suffix . '\0',
				$url
			);
			unset($attributes->suffix);
		}
	}

	protected function getIntroImage($data, $attributes)
	{
		return $this->getArticleImageDataByType('intro', $data, $attributes);
	}

	protected function getFulltextImage($data, $attributes)
	{
		return $this->getArticleImageDataByType('fulltext', $data, $attributes);
	}

	protected function getArticleImageDataByType($type = 'intro', $data, $attributes)
	{
		$type = $type == 'fulltext' ? 'fulltext' : 'intro';

		switch ($data)
		{
			case 'url':
				return $this->getArticleImageUrlByType($type, $attributes);

			case 'caption':
				return $this->item->getFromGroup('images', 'image_' . $type . '_caption');

			case '':
				return $this->getArticleImageTagByType($type, $attributes);

			default:
				return $this->getArticleImageAttributeByType($data, $type, $attributes);
		}
	}

	protected function getArticleImageUrlByType($type = 'intro', &$attributes)
	{
		$url = $this->item->getFromGroup('images', 'image_' . $type);

		if (empty($url))
		{
			return '';
		}

		$image = ['url' => $url];

		$this->prepareImageUrl($image['url'], $attributes);

		$this->setResizedImage($image, $attributes);

		return $image['url'];
	}

	protected function getArticleImageTagByType($type = 'intro', $attributes)
	{
		$url = $this->getArticleImageUrlByType($type, $attributes);

		if (empty($url))
		{
			return '';
		}

		$layout = isset($attributes->layout) ? $attributes->layout : '';
		unset($attributes->layout);

		$float   = $this->item->getFromGroup('images', 'float_' . $type);
		$alt     = $this->item->getFromGroup('images', 'image_' . $type . '_alt');
		$caption = $this->item->getFromGroup('images', 'image_' . $type . '_caption');

		$attributes->src   = $url;
		$attributes->alt   = isset($attributes->alt) ? $attributes->alt : $alt;
		$attributes->title = isset($attributes->title) ? $attributes->title : $caption;
		$attributes->class = isset($attributes->class) ? $attributes->class : 'item-image-' . $type;

		self::setAltAndTitle($type, $attributes);

		if ($layout == 'true')
		{
			$layout = 'joomla.content.' . ($type == 'fulltext' ? 'full' : $type) . '_image';
		}

		if (empty($layout) || $layout == 'false')
		{
			return $this->getImageHtml($attributes);
		}

		if ( ! class_exists('ContentModelArticle'))
		{
			require_once JPATH_SITE . '/components/com_content/models/article.php';
		}

		if ( ! class_exists('ContentHelperRoute'))
		{
			require_once JPATH_SITE . '/components/com_content/helpers/route.php';
		}

		$model = JModel::getInstance('article', 'contentModel');

		if ( ! method_exists($model, 'getItem'))
		{
			return null;
		}

		$item = $model->getItem($this->item->get('id'));

		$item->slug        = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;
		$item->catslug     = $item->category_alias ? ($item->catid . ':' . $item->category_alias) : $item->catid;
		$item->parent_slug = $item->parent_alias ? ($item->parent_id . ':' . $item->parent_alias) : $item->parent_id;

		if ($item->parent_alias === 'root')
		{
			$item->parent_slug = null;
		}

		$item->readmore_link = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catid, $item->language));

		$item->images = json_encode(array_merge((array) $attributes, [
			'image_' . $type              => $url,
			'image_' . $type . '_alt'     => $attributes->alt,
			'image_' . $type . '_title'   => $attributes->title,
			'image_' . $type . '_caption' => $caption,
			'float_' . $type              => $float,
			'image'                       => $url,
			'image_alt'                   => $attributes->alt,
			'image_title'                 => $attributes->title,
			'image_caption'               => $caption,
			'float'                       => $float,
		]));

		return JLayoutHelper::render($layout, $item);
	}

	protected function getArticleImageAttributeByType($key, $type = 'intro', $attributes)
	{
		$img_tag = $this->getArticleImageTagByType($type, $attributes);

		return $this->getImageAttribute($key, $img_tag);
	}

	protected function getImageAttribute($key, $html)
	{
		$tag_attributes = RL_HtmlTag::getAttributes($html);

		if (isset($tag_attributes[$key]))
		{
			return $tag_attributes[$key];
		}

		if ( ! in_array($key, ['width', 'height']))
		{
			return '';
		}

		$url = $tag_attributes['src'];

		if (RL_File::isExternal($url))
		{
			return '';
		}

		$dimensions = RL_Image::getDimensions($url);

		return isset($dimensions->{$key}) ? $dimensions->{$key} : '';
	}

	protected function getCategoryImage($data, $attributes)
	{
		$params = json_decode($this->item->get('category-params', '{}'));

		if (empty($params))
		{
			return '';
		}

		switch ($data)
		{
			case 'url':
				return $this->getCategoryImageUrl($params, $attributes);

			case '':
				return $this->getCategoryImageTag($params, $attributes);

			default:
				return $this->getCategoryImageAttribute($data, $params, $attributes);
		}
	}

	protected function getCategoryImageUrl($params, &$attributes)
	{
		if (empty($params->image))
		{
			return '';
		}

		$image = ['url' => $params->image];

		$this->prepareImageUrl($image['url'], $attributes);

		$this->setResizedImage($image, $attributes);

		return $image['url'];
	}

	protected function getCategoryImageTag($params, $attributes)
	{
		$url = $this->getCategoryImageUrl($params, $attributes);

		if (empty($url))
		{
			return '';
		}

		$attributes->src   = $url;
		$attributes->class = isset($attributes->class) ? $attributes->class : 'category-image';
		$attributes->alt   = isset($attributes->alt)
			? $attributes->alt
			: (isset($params->image_alt) ? $params->image_alt : '');

		self::setAltAndTitle('category', $attributes);

		return $this->getImageHtml($attributes);
	}

	protected function getCategoryImageAttribute($key, $params, $attributes)
	{
		$img_tag = $this->getCategoryImageTag($params, $attributes);

		return $this->getImageAttribute($key, $img_tag);
	}

	protected function getContentImageCount()
	{
		$images = $this->getContentImages();

		return count($images);
	}

	protected function getContentImage($count, $data, $attributes)
	{
		if ( ! is_numeric($count))
		{
			return '';
		}

		$images = $this->getContentImages($attributes);

		if (empty($images))
		{
			return '';
		}

		if ( ! empty($attributes->filter))
		{
			$this->filterImages($images, $attributes->filter);
		}

		// Subtract 1 off the count, as te images array starts at 0
		$count = $count - 1;

		if ( ! isset($images[$count]))
		{
			return '';
		}

		$image = $images[$count];

		switch ($data)
		{
			case 'url':
				return $this->getContentImageUrl($image, $attributes);

			case 'thumb-url':
				return $this->getContentImageThumbUrl($image);

			case 'thumb':
				return $this->getContentImageThumbTag($image, $attributes);

			case '':
				return $this->getContentImageTag($image, $attributes);

			default:
				return $this->getContentImageAttribute($data, $image, $attributes);
		}
	}

	protected function getContentImageAttribute($key, $image, $attributes)
	{
		$img_tag = $this->getContentImageTag($image, $attributes);

		return $this->getImageAttribute($key, $img_tag);
	}

	protected function filterImages(&$images, $filter = '')
	{
		foreach ($images as $i => $image)
		{
			if (RL_RegEx::match($filter, $image['url']))
			{
				continue;
			}

			unset($images[$i]);
		}

		$images = array_values($images);
	}

	protected function setResizedImage(&$image, &$attributes)
	{
		if ( ! $this->shouldResize($image, $attributes))
		{
			unset($attributes->resize);
			$this->calculateWidthHeight($image, $attributes);

			return;
		}

		$params = Params::get();

		$this->setWidthHeight($attributes);
		unset($attributes->resize);

		$new_width  = isset($attributes->width) ? $attributes->width : 0;
		$new_height = isset($attributes->height) ? $attributes->height : 0;

		$resized = RL_Image::resize(
			$image['url'],
			$new_width,
			$new_height,
			dirname($image['url']) . '/' . $params->resize_folder,
			$params->resize_quality
		);

		if ( ! $resized || $resized == $image['url'])
		{
			return;
		}

		$image['url']       = $resized;
		$attributes->src    = $resized;
		$attributes->width  = $new_width;
		$attributes->height = $new_height;
	}

	protected function shouldResize(&$image, $attributes)
	{
		// in data tag: resize="false"
		if (isset($attributes->resize) && ! $attributes->resize)
		{
			return false;
		}

		// don't resize external images
		if (RL_File::isExternal($image['url']))
		{
			return false;
		}

		$url = ltrim(str_replace(JUri::root(), '', $image['url']), '/');

		// image doesn't exist
		if ( ! file_exists(JPATH_SITE . '/' . $url))
		{
			return false;
		}

		$image['url']    = $url;
		$attributes->src = $url;

		// image filetype is not supported or enabled
		if ( ! $this->isEnabledResizeFiletype($image))
		{
			return false;
		}

		// in data tag: resize="true"
		if ( ! empty($attributes->resize))
		{
			return true;
		}

		switch ($this->getResizeMethod())
		{
			// Resize is off
			case 'no':
				return false;

			// Resize by default = always resize
			case 'yes':
				return true;

			// Resize only if width or height are set
			case 'standard':
			default:
				return ( ! empty($attributes->width) || ! empty($attributes->height));
		}
	}

	protected function calculateWidthHeight($image, &$attributes)
	{
		// image doesn't exist
		if ( ! file_exists(JPATH_SITE . '/' . $image['url']))
		{
			return;
		}

		// width and height are already both set
		if ( ! empty($attributes->width) && ! empty($attributes->height))
		{
			return;
		}

		// no width or height set, so can't calculate
		if (empty($attributes->width) && empty($attributes->height))
		{
			return;
		}

		if ( ! $dimensions = $this->getDimensionsFromTag($image))
		{
			return;
		}

		$attributes->width  = isset($attributes->width) ? $attributes->width : round($dimensions->width / $dimensions->height * $attributes->height);
		$attributes->height = isset($attributes->height) ? $attributes->height : round($dimensions->height / $dimensions->width * $attributes->width);
	}

	protected function getDimensionsFromTag($image)
	{
		if (RL_File::isInternal($image['url']))
		{
			return RL_Image::getDimensions($image['url']);
		}

		if ( ! isset($image['tag']))
		{
			return false;
		}

		$tag_attributes = RL_HtmlTag::getAttributes($image['tag']);

		if ( ! isset($tag_attributes['width']) || ! isset($tag_attributes['height']))
		{
			return false;
		}

		return (object) [
			'width'  => $tag_attributes['width'],
			'height' => $tag_attributes['height'],
		];
	}

	protected function setWidthHeight(&$attributes)
	{
		if ( ! empty($attributes->width) || ! empty($attributes->height))
		{
			return;
		}

		$params = Params::get();

		if ($this->getResizeMethod() != 'yes' && empty($attributes->resize))
		{
			return;
		}

		// Force width/height to param settings
		$attributes->width  = $params->resize_width;
		$attributes->height = $params->resize_height;

		if ($params->resize_type == 'crop')
		{
			return;
		}

		if ($params->resize_using == 'width')
		{
			$attributes->height = 0;

			return;
		}

		$attributes->width = 0;
	}

	protected function isEnabledResizeFiletype($image)
	{
		$filetypes = RL_Array::toArray(Params::get()->resize_filetypes);

		$extension = RL_File::getExtension($image['url']);
		$extension = str_replace(
			['jpeg'],
			['jpg'],
			strtolower($extension)
		);

		return in_array($extension, $filetypes);
	}

	protected function getResizeMethod()
	{
		$params = Params::get();

		if (is_numeric($params->resize_images))
		{
			return $params->resize_images ? 'yes' : 'no';
		}

		return 'standard';
	}

	protected function getContentImageUrl($image, &$attributes)
	{
		$this->prepareImageUrl($image['url'], $attributes);

		$this->setResizedImage($image, $attributes);

		return $image['url'];
	}

	protected function getContentImageThumbUrl($image)
	{
		return RL_RegEx::replace('(\.[a-z]+)$', '_t\1', $image['url']);
	}

	protected function getContentImageThumbTag($image, $attributes)
	{
		$tag            = $image['tag'];
		$tag_attributes = RL_HtmlTag::getAttributes($tag);

		$attributes->src    = $image['url'];
		$attributes->alt    = isset($attributes->alt)
			? $attributes->alt
			: (isset($tag_attributes['alt']) ? $tag_attributes['alt'] : '');
		$attributes->title  = isset($attributes->title)
			? $attributes->title
			: (isset($tag_attributes['title']) ? $tag_attributes['title'] : '');
		$attributes->suffix = '_t';
		$attributes->width  = '';
		$attributes->height = '';
		self::setAltAndTitle('content', $attributes);

		return self::getImageHtmlWithAttributes($tag, $attributes);
	}

	protected function getContentImageTag($image, $attributes)
	{
		$image['url'] = $this->getContentImageUrl($image, $attributes);
		$tag          = $image['tag'];

		$tag_attributes = RL_HtmlTag::getAttributes($tag);

		$attributes->src   = $image['url'];
		$attributes->alt   = isset($attributes->alt)
			? $attributes->alt
			: (isset($tag_attributes['alt']) ? $tag_attributes['alt'] : '');
		$attributes->title = isset($attributes->title)
			? $attributes->title
			: (isset($tag_attributes['title']) ? $tag_attributes['title'] : '');

		self::setAltAndTitle('content', $attributes);

		return self::getImageHtmlWithAttributes($tag, $attributes);
	}

	protected function getContentImages()
	{
		$images = $this->item->get('_images');

		if ( ! is_null($images) && is_array($images))
		{
			return $images;
		}

		$text = $this->item->get('text');

		RL_RegEx::matchAll(
			'(?<tag><img\s[^>]*src=([\'"])(?<url>.*?)\2[^>]*>)',
			$text,
			$images
		);

		$this->item->set('_images', $images);

		return $images;
	}

	public static function getImageHtml($attributes)
	{
		$attributes = (object) $attributes;

		$src   = ' src="' . htmlspecialchars($attributes->src) . '"';
		$alt   = ' alt="' . htmlspecialchars(! empty($attributes->alt) ? $attributes->alt : '') . '"';
		$title = ! empty($attributes->title) ? ' title="' . htmlspecialchars($attributes->title) . '"' : '';
		$class = ! empty($attributes->class) ? ' class="' . htmlspecialchars($attributes->class) . '"' : '';

		unset($attributes->src);
		unset($attributes->alt);
		unset($attributes->title);
		unset($attributes->class);

		$tag = '<img' . $src . $alt . $title . $class . '">';

		$image = self::getImageHtmlWithAttributes($tag, $attributes);

		return $image;
	}

	static protected function getImageHtmlWithAttributes($tag, $attributes)
	{
		if (empty($attributes))
		{
			return $tag;
		}

		$attributes = (object) $attributes;

		$outer_class = isset($attributes->{'outer-class'}) ? $attributes->{'outer-class'} : '';
		unset($attributes->{'outer-class'});

		$tag_attributes = RL_HtmlTag::getAttributes($tag);

		$tag_attributes = (object) array_merge($tag_attributes, (array) $attributes);

		$image = '<img ' . RL_HtmlTag::flattenAttributes($tag_attributes) . ' />';

		if ( ! $outer_class)
		{
			return $image;
		}

		return '<div class="' . htmlspecialchars($outer_class) . '">'
			. $image
			. '</div>';
	}

	static public function setAltAndTitle($type = 'intro', &$attributes, $data = null)
	{
		self::crossFillAltAndTitle($attributes);

		$params = Params::get();

		if ( ! empty($attributes->alt) && ! empty($attributes->title))
		{
			return;
		}

		if ( ! isset($params->{'image_titles_' . $type}) || ! $params->{'image_titles_' . $type})
		{
			return;
		}

		switch ($params->{'image_titles_' . $type})
		{
			case 'file':
				$title = self::getTitleFromFile($attributes->src);
				break;

			case 'article':
				$title = self::getTitleFromArticle();
				break;

			case 'category':
				$title = self::getTitleFromCategory();
				break;

			case 'field':
				$title = self::getTitleFromCustomField($data);
				break;

			default:
			case 'none':
				return;
		}

		if (empty($attributes->alt))
		{
			$attributes->alt = $title;
		}
		if (empty($attributes->title))
		{
			$attributes->title = $title;
		}
	}

	static protected function crossFillAltAndTitle(&$attributes)
	{
		$params = Params::get();

		if ( ! $params->image_titles_cross_fill)
		{
			return;
		}

		if (empty($attributes->alt) && ! empty($attributes->title))
		{
			$attributes->alt = $attributes->title;
		}
		if (empty($attributes->title) && ! empty($attributes->alt))
		{
			$attributes->title = $attributes->alt;
		}
	}

	static protected function getTitleFromArticle()
	{
		if (is_null(self::$static_item))
		{
			return '';
		}

		return self::$static_item->get('title');
	}

	static protected function getTitleFromCategory()
	{
		if (is_null(self::$static_item))
		{
			return '';
		}

		return self::$static_item->get('category-title');
	}

	static protected function getTitleFromCustomField($field)
	{
		return isset($field->label) ? $field->label : '';
	}

	static protected function getTitleFromFile($url)
	{
		$params = Params::get();

		$title = self::getCleanTitle($url);

		switch ($params->image_titles_case)
		{
			case 'lowercase':
				return RL_String::strtolower($title);

			case 'uppercase':
				return RL_String::strtoupper($title);

			case 'uppercasefirst':
				return RL_String::strtoupper(RL_String::substr($title, 0, 1))
					. RL_String::strtolower(RL_String::substr($title, 1));

			case 'titlecase':
				return function_exists('mb_convert_case')
					? mb_convert_case(RL_String::strtolower($title), MB_CASE_TITLE)
					: ucwords(strtolower($title));

			case 'titlecase_smart':
				$title           = function_exists('mb_convert_case')
					? mb_convert_case(RL_String::strtolower($title), MB_CASE_TITLE)
					: ucwords(strtolower($title));
				$lowercase_words = explode(',', ' ' . str_replace(',', ' , ', RL_String::strtolower($params->image_titles_lowercase_words)) . ' ');

				return str_ireplace($lowercase_words, $lowercase_words, $title);
		}

		return $title;
	}

	static protected function getCleanTitle($url)
	{
		$title = self::getCleanFileName($url);

		// Replace dashes with spaces
		return str_replace(['-', '_'], ' ', $title);
	}

	static protected function getCleanFileName($url)
	{
		$title = RL_File::getFileName($url);

		// Remove trailing dimensions
		$title = RL_RegEx::replace('[_-][0-9]+x[0-9]+?$', '', $title);

		return $title;
	}
}
