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


use RegularLabs\Library\Image as RL_Image;

class Image
{
	use ImageTraits;

	public function __construct($string = '', $settings = null)
	{
		$this->setSettings($settings);
		$this->setData($string);

		$main_image = RL_Image::isResized($this->getUrl(), $this->settings->{'thumbnail-folder'}, $this->getSuffix());

		if ($main_image
			&& ! isset($settings['thumbnail-width'])
			&& ! isset($settings['thumbnail-height'])
		)
		{
			$this->settings->{'thumbnail-width'}       = RL_Image::getWidth($this->getUrl());
			$this->settings->{'thumbnail-height'}      = RL_Image::getHeight($this->getUrl());
			$this->settings->{'thumbnail-resize-type'} = 'crop';
		}

		if ($main_image)
		{
			$this->setFromUrl($main_image);
		}

		$this->setTexts();
	}

	public function __get($name)
	{
		switch ($name)
		{
			case 'thumbnail';
				if ( ! isset($this->thumbnail))
				{
					$this->setThumbnail();
				}

				return $this->thumbnail;
		}

		return null;
	}

	private function setThumbnail()
	{
		list($width, $height) = $this->getThumbnailDimensions();

		$images = RL_Image::getUrls(
			$this->getUrl(),
			$width,
			$height,
			$this->settings->{'thumbnail-folder'},
			$this->settings->{'create-thumbnails'},
			$this->settings->{'thumbnail-quality'},
			$this->getSuffix()
		);

		$this->setFromUrl($images->original);

		$this->attributes->width  = $width;
		$this->attributes->height = $height;

		$this->thumbnail = new Thumbnail($images->resized, $this, $this->settings);
	}

	private function getThumbnailDimensions()
	{
		$width  = ! empty($this->attributes->width) ? $this->attributes->width : 0;
		$height = ! empty($this->attributes->height) ? $this->attributes->height : 0;

		if ($width || $height)
		{
			return [$width, $height];
		}

		return $this->getDefaultThumbnailDimensions();
	}

	private function getDefaultThumbnailDimensions()
	{
		$width  = $this->settings->{'thumbnail-width'};
		$height = $this->settings->{'thumbnail-height'};

		if ($this->settings->{'thumbnail-resize-type'} == 'crop')
		{
			return [$width, $height];
		}

		switch ($this->settings->{'thumbnail-resize-using'})
		{
			case 'width':
				return [$width, 0];

			case 'height':
			default:
				return [0, $height];
		}
	}

	private function getSuffix()
	{
		if ( ! $this->settings->{'thumbnail-legacy'})
		{
			return '';
		}

		return isset($this->settings->{'thumbnail-suffix'})
			? $this->settings->{'thumbnail-suffix'}
			: '';
	}

	public function getImageTextsFromDataFile()
	{
		$file_data = $this->getImageDataFromDataFile();

		if (empty($file_data) || ! is_array($file_data))
		{
			return [];
		}

		return $file_data;
	}

	public function getImageDataFromDataFile()
	{
		if ( ! is_null(self::$data_txt))
		{
			return self::$data_txt;
		}

		$folder = isset($this->main_image) ? $this->main_image->getPathFolder() : $this->getPathFolder();

		if ( ! file_exists($folder . '/data.txt'))
		{
			return [];
		}

		$data = file_get_contents($folder . '/data.txt');

		$data = str_replace("\r", '', $data);
		$data = explode("\n", $data);

		$array = [];
		foreach ($data as $data_line)
		{
			if (empty($data_line)
				|| $data_line[0] == '#'
				|| strpos($data_line, '=') === false
			)
			{
				continue;
			}
			list($key, $val) = explode('=', $data_line, 2);
			$array[$key] = $val;
		}

		self::$data_txt = $array;

		return $array;
	}
}
