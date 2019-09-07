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


use RegularLabs\Library\File as RL_File;
use RegularLabs\Library\Image as RL_Image;

class Thumbnail
{
	use ImageTraits;

	private $source;
	private $main_image;

	public function __construct($file, Image $main_image, $settings)
	{
		$this->setSettings($settings);
		$this->setFromUrl($file);
		$this->setAttributes($main_image->attributes);

		$this->source     = $main_image->getUrl();
		$this->main_image = $main_image;

		$this->attributes->width  = isset($this->attributes->width) ? $this->attributes->width : 0;
		$this->attributes->height = isset($this->attributes->height) ? $this->attributes->height : 0;

		if ( ! RL_File::isExternal($file) && file_exists($file))
		{
			$this->attributes->width  = RL_Image::getWidth($file);
			$this->attributes->height = RL_Image::getHeight($file);
		}

		$this->setTexts();
	}

	public function getTexts($count = 0, $jtext = false)
	{
		$title = ! empty($this->attributes->title) ? $this->attributes->title : '';
		$title = $this->getImageTextByType('thumbnail_title', $title, $count, $jtext);

		$alt = ! empty($this->attributes->alt) ? $this->attributes->alt : $title;
		$alt = $this->getImageTextByType('thumbnail_alt', $alt, $count, $jtext);

		$description = ! empty($this->attributes->description)
			? $this->attributes->description
			: $this->getImageTextByType('description', '', $count, $jtext);

		return (object) compact('title', 'alt', 'description');
	}

	public function getDataFolder()
	{
		return $this->main_image->getFolder();
	}
}
