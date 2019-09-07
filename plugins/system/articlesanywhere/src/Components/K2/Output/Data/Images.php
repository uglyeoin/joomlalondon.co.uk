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

use Joomla\CMS\Uri\Uri as JUri;

class Images extends \RegularLabs\Plugin\System\ArticlesAnywhere\Output\Data\Images
{
	public function get($key, $attributes)
	{
		$key = str_replace('_', '-', $key);

		switch ($key)
		{
			case 'image-url':
				return $this->getItemImageUrl();

			case 'image':
				return $this->getItemImage($attributes);

			case 'thumb-url':
			case 'image-thumb-url':
				return $this->getItemThumbUrl();

			case 'image-thumb':
				return $this->getItemThumb($attributes);

			default:
				return parent::get($key, $attributes);
		}
	}

	protected function getItemImageUrl()
	{
		$file = 'media/k2/items/cache/' . md5("Image" . $this->item->getId()) . '_L.jpg';

		if ( ! file_exists(JPATH_SITE . '/' . $file))
		{
			return '';
		}

		return JUri::root() . $file;
	}

	protected function getItemImage($attributes)
	{
		$file = 'media/k2/items/cache/' . md5("Image" . $this->item->getId()) . '_L.jpg';

		if ( ! file_exists(JPATH_SITE . '/' . $file))
		{
			return '';
		}

		$url = JUri::root() . $file;

		return self::getImageHtml(
			$url,
			$this->item->get('title'),
			$this->item->get('image_caption'),
			'k2_image',
			$attributes,
			false
		);
	}

	protected function getItemThumbUrl()
	{
		$file = 'media/k2/items/cache/' . md5("Image" . $this->item->getId()) . '_S.jpg';

		if ( ! file_exists(JPATH_SITE . '/' . $file))
		{
			return '';
		}

		return JUri::root() . $file;
	}

	protected function getItemThumb($attributes)
	{
		$file = 'media/k2/items/cache/' . md5("Image" . $this->item->getId()) . '_S.jpg';

		if ( ! file_exists(JPATH_SITE . '/' . $file))
		{
			return '';
		}

		$url = JUri::root() . $file;

		return self::getImageHtml(
			$url,
			$this->item->get('title'),
			$this->item->get('image_caption'),
			'k2_image',
			$attributes
		);
	}
}
