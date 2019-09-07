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


use Joomla\CMS\Language\Text as JText;
use JUri;
use RegularLabs\Library\ArrayHelper as RL_Array;
use RegularLabs\Library\File as RL_File;
use RegularLabs\Library\HtmlTag as RL_HtmlTag;
use RegularLabs\Library\Uri as RL_Uri;

Trait ImageTraits
{
	var $settings     = null;
	var $attributes   = null;
	var $original_url = null;
	var $folder       = '';
	var $file         = '';
	var $image_data   = [];
	var $is_external  = null;

	public function setData($string)
	{
		if (empty($string))
		{
			return;
		}

		if (is_object($string))
		{
			$this->setFromObject($string);

			return;
		}

		$this->setFromUrl($string);
	}

	public function setFromObject($object)
	{
		if (isset($object->src))
		{
			$this->setFromUrl($object->src);
			unset($object->src);
		}

		if (isset($object->url))
		{
			$this->setFromUrl($object->url);
			unset($object->url);
		}

		$this->setAttributes($object);
	}

	public function setFromUrl($url)
	{
		$this->url = $url;
	}

	public function setAttributes($attributes)
	{
		$attributes = (array) clone $attributes;

		foreach ($attributes as $key => $val)
		{
			if (strpos($key, 'data-modal-') !== 0)
			{
				continue;
			}

			$attributes[substr($key, strlen('data-modal-'))] = $val;
			unset($attributes[$key]);
		}

		$this->attributes = (object) $attributes;
	}

	public function setSettings($object = [])
	{
		$this->settings = clone Params::getSettings();

		if (empty($object))
		{
			return;
		}

		$object = (object) $object;

		if ( ! empty($object->{'thumbnail-width'}) && empty($object->{'thumbnail-height'}))
		{
			$object->{'thumbnail-height'} = 0;
		}

		if ( ! empty($object->{'thumbnail-height'}) && empty($object->{'thumbnail-width'}))
		{
			$object->{'thumbnail-width'} = 0;
		}

		if ( ! empty($object->{'thumbnail-width'}) && ! empty($object->{'thumbnail-height'}))
		{
			$object->{'thumbnail-crop'} = true;
		}

		foreach ($object as $key => $value)
		{
			$key = str_replace('_', '-', $key);

			$this->settings->{$key} = $value;
		}
	}

	private function setTexts()
	{
		$texts = self::getTexts();

		if (is_null($this->attributes))
		{
			$this->attributes = (object) [];
		}

		foreach ($texts as $key => $value)
		{
			$this->attributes->{$key} = JText::_($value);
		}
	}

	public function getTexts($count = 0, $jtext = false)
	{
		$texts = self::getTextsFromData($count, $jtext);

		if (isset($this->attributes->title) && $this->settings->{'images-use-title-attribute'})
		{
			$texts->{$this->settings->{'images-use-title-attribute'}} = $this->attributes->title;
		}

		if (isset($this->attributes->alt) && $this->settings->{'images-use-alt-attribute'})
		{
			$texts->{$this->settings->{'images-use-alt-attribute'}} = $this->attributes->alt;
		}

		if (empty($texts->title) && $this->settings->{'auto-titles'})
		{
			$texts->title = File::getTitle($this->getPath(), $this->settings->{'title-case'});
		}

		if (empty($texts->alt))
		{
			$texts->alt = $texts->title;
		}

		return $texts;
	}

	public function getTextsFromData($count = 0, $jtext = false)
	{
		$title = ! empty($this->settings->title) ? $this->settings->title : '';
		$title = $this->getImageTextByType('title', $title, $count, $jtext);

		$alt = ! empty($this->settings->alt) ? $this->settings->alt : '';
		$alt = $this->getImageTextByType('alt', $alt, $count, $jtext);

		$description = ! empty($this->settings->description) ? $this->settings->description : '';
		$description = $this->getImageTextByType('description', $description, $count, $jtext);

		return (object) compact('title', 'alt', 'description');
	}

	public function getImageTextByType($type, $default, $count = 0, $jtext = false)
	{
		$text = $this->getImageRawTextByType($type, $count);

		if ( ! $text)
		{
			return $default;
		}

		if ($jtext)
		{
			$text = JText::_($text);
		}

		return $text;
	}

	public function getImageRawTextByType($type, $count = 0)
	{
		$data_txt = ImageData::get($this->getDataFolder());

		if ($count && isset($data_txt[$type . '_' . $count]))
		{
			return $data_txt[$type . '_' . $count];
		}

		if (isset($data_txt[$type . '_' . $this->getFileName()]))
		{
			return $data_txt[$type . '_' . $this->getFileName()];
		}

		$title = File::getCleanFileName($this->getFileName());

		if (isset($data_txt[$type . '_' . $title]))
		{
			return $data_txt[$type . '_' . $title];
		}

		return isset($data_txt[$type]) ? $data_txt[$type] : '';
	}

	public function cleanUrl()
	{
		$url = ltrim(utf8_decode($this->url), '/');

		if (RL_File::isExternal($url) || strpos($url, JUri::root()) !== 0)
		{
			return $url;
		}

		return substr($url, strlen(JUri::root()));
	}

	public function getUrl()
	{
		return $this->cleanUrl();
	}

	public function getHref()
	{
		if ($this->isExternal())
		{
			return $this->getUrl();
		}

		return RL_Uri::route($this->getUrl());
	}

	public function getPath()
	{
		return JPATH_SITE . '/' . $this->getUrl();
	}

	public function getFolder()
	{
		return RL_File::getDirName($this->getUrl());
	}

	public function getDataFolder()
	{
		return $this->getFolder();
	}

	public function getPathFolder()
	{
		return RL_File::getDirName($this->getPath());
	}

	public function getBaseName($lowercase = false)
	{
		return RL_File::getBaseName($this->getPath(), $lowercase);
	}

	public function getFileName($lowercase = false)
	{
		return RL_File::getFileName($this->getPath(), $lowercase);
	}

	public function exists()
	{
		if (empty($this->url))
		{
			return false;
		}

		if ($this->isExternal())
		{
			return true;
		}

		return file_exists($this->getPath());
	}

	public function isExternal()
	{
		if ( ! is_null($this->is_external))
		{
			return $this->is_external;
		}

		$this->is_external = RL_File::isExternal($this->getUrl());

		return $this->is_external;
	}

	public function getHtmlAttributes($overrides = [], $defaults = [])
	{
		$attributes = $defaults;

		foreach ($this->attributes as $key => $val)
		{
			$attributes[$key] = $val;
		}

		foreach ($overrides as $key => $val)
		{
			$attributes[$key] = $val;
		}
		$attributes = RL_Array::removeEmpty($attributes);

		// Remove 0 width and height
		if (empty($attributes['width']))
		{
			unset($attributes['width']);
		}

		if (empty($attributes['height']))
		{
			unset($attributes['height']);
		}

		return RL_HtmlTag::flattenAttributes($attributes);
	}

	public function getHtmlTag($attributes = [])
	{
		$overrides = [
			'src' => $this->getHref(),
		];

		if ( ! empty($this->attributes->width))
		{
			$overrides['width'] = $this->attributes->width;
		}

		if ( ! empty($this->attributes->height))
		{
			$overrides['height'] = $this->attributes->height;
		}

		$overrides = array_merge((array) $attributes, $overrides);

		return '<img ' . $this->getHtmlAttributes($overrides) . '>';
	}
}
