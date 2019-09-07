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


use RegularLabs\Library\RegEx as RL_RegEx;
use RegularLabs\Plugin\System\ArticlesAnywhere\Collection\Item;
use RegularLabs\Plugin\System\ArticlesAnywhere\Config;
use RegularLabs\Plugin\System\ArticlesAnywhere\Output\Values;

class Videos extends Data
{

	public function __construct(Config $config, Item $item, Values $values)
	{
		parent::__construct($config, $item, $values);

		$this->youtube = new VideosYoutube($config, $item, $values);
		$this->vimeo   = new VideosVimeo($config, $item, $values);
	}

	public function get($key, $attributes)
	{
		$regex = '^(?<closing>/?)(?:video-)?(?<type>youtube|vimeo)-(?<count>count|[0-9]+)(-(?<data>[a-z][a-z0-9-_]*))?$';

		RL_RegEx::match($regex, $key, $video_tag);

		if (empty($video_tag))
		{
			return null;
		}

		// a leading slash is (currently) only used to close links
		if ( ! empty($video_tag['closing']))
		{
			return '</a>';
		}

		$data = isset($video_tag['data']) ? $video_tag['data'] : '';

		$data = str_replace(
			[
				'thumbnail',
				'url-iframe',
				'url-short',
			],
			[
				'thumb',
				'iframe-url',
				'short-url',
			],
			$data
		);
		$data = RL_RegEx::replace('-?(tag|img|iframe)$', '', $data);

		return $this->getByType($video_tag['type'], $video_tag['count'], $data, $attributes);
	}

	protected function getByType($type, $count, $data, $attributes)
	{
		switch ($type)
		{
			case 'vimeo':
				return $this->vimeo->getOutput($count, $data, $attributes);

			case 'youtube':
			default:
				return $this->youtube->getOutput($count, $data, $attributes);
		}
	}
}
