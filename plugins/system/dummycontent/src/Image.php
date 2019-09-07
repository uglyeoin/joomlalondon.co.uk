<?php
/**
 * @package         Dummy Content
 * @version         6.0.2PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2019 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\DummyContent;

defined('_JEXEC') or die;



class Image
{
	public static function render(&$options)
	{
		$params = Params::get();

		$options->width  = isset($options->width) ? (int) $options->width : (int) $params->image_width;
		$options->height = isset($options->height) ? (int) $options->height : (int) $params->image_height;

		$title = isset($options->title) ? ' title="' . $options->title . '"' : '';
		$alt   = ' alt="' . (isset($options->alt) ? $options->alt : (isset($options->title) ? $options->title : '')) . '"';
		$class = 'dummycontent_image ' . (isset($options->class) ? $options->class : '');
		$float = isset($options->float) ? ' style="float:' . $options->float . ';"' : '';

		$image_service = self::getService($options);
		$url           = self::getUrl($image_service, $options);

		// make the url unique
		self::addToUrl($url, uniqid());

		return '<img src="' . $url . '" width="' . $options->width . '" height="' . $options->height . '"' . $alt . $title . 'class="' . trim($class) . '"' . $float . '>';
	}

	private static function addToUrl(&$url, $key, $value = null)
	{
		if (empty($key))
		{
			return;
		}

		if ($value == '')
		{
			return;
		}

		$attribute = $key;

		if ( ! is_null($value))
		{
			$attribute .= '=' . $value;
		}

		$url .= strpos($url, '?') === false ? '?' : '&';
		$url .= $attribute;
	}

	private static function getText2(&$options)
	{
		if (isset($options->text))
		{
			$options->text = trim($options->text);
			switch ($options->text)
			{
				case '':
				case 'none':
					return '';

				case 'dimensions':
				case 'dimentions':
					return $options->width . 'x' . $options->height;

				default:
					return $options->text;
			}
		}

		$params = Params::get();

		switch ($params->image_show_text)
		{
			case 'none':
				return '';

			case 'dimensions':
				return $options->width . 'x' . $options->height;

			default:
				return $params->image_text;
		}
	}

	private static function getText(&$options)
	{
		if (isset($options->text))
		{
			$options->text = trim($options->text);
			switch ($options->text)
			{
				case '':
				case 'none':
					return '+';

				case 'dimensions':
				case 'dimentions':
					return '';

				default:
					return $options->text;
			}
		}

		$params = Params::get();

		switch ($params->image_show_text)
		{
			case 'none':
				return '+';

			case 'dimensions':
				return '';

			default:
				return $params->image_text ?: '+';
		}
	}

	private static function getColor(&$options)
	{
		$params = Params::get();

		if ( ! isset($options->color) && $params->image_background_color_random)
		{
			return self::getRandomColor();
		}

		return self::getColorValue($options, 'color', $params->image_background_color);
	}

	private static function getForgroundColor(&$options)
	{
		$params = Params::get();

		return self::getColorValue($options, 'text_color', $params->image_foreground_color);
	}

	private static function getColorValue(&$options, $key = 'color', $default = '')
	{
		if ( ! isset($options->{$key}))
		{
			return self::removeLeadingHash($default);
		}

		if ($options->{$key} == 'random')
		{
			return self::getRandomColor();
		}

		return self::removeLeadingHash($options->{$key});
	}

	private static function getRandomColor()
	{
		$params = Params::get();

		$r = rand($params->image_background_color_random_start, $params->image_background_color_random_end);
		$g = rand($params->image_background_color_random_start, $params->image_background_color_random_end);
		$b = rand($params->image_background_color_random_start, $params->image_background_color_random_end);

		return dechex($r) . dechex($g) . dechex($b);
	}

	private static function removeLeadingHash($string)
	{
		if (substr($string, 0, 1) != '#')
		{
			return $string;
		}

		return substr($string, 1);
	}

	private static function getService(&$options)
	{
		$params = Params::get();

		$image_service = isset($options->service) ? $options->service : $params->image_service;

		$image_service = strtolower($image_service);

		if (strpos($image_service, '.') !== false)
		{
			$image_service = substr($image_service, 0, strpos($image_service, '.'));
		}

		return $image_service;
	}

	private static function getUrl($service, &$options)
	{
		switch ($service)
		{
			case 'fakeimg':
				return self::fakeimg($options);

			case 'placeskull':
				return self::placeskull($options);

			case 'picsum':
				return self::picsum($options);

			case 'placeimg':
				return self::placeimg($options);

			case 'placebeard':
				return self::placebeard($options);

			case 'pickadummy':
			default:
				return self::pickadummy($options);
		}
	}

	private static function pickadummy(&$options)
	{
		$params = Params::get();

		$greyscale = isset($options->greyscale) ? $options->greyscale : $params->image_greyscale;
		$colorize  = self::getColorValue($options, 'colorize', $params->image_colorize);

		$dimensions = isset($options->dimensions) ? $options->dimensions : $params->image_show_dimensions;

		$text         = self::getText2($options);
		$color        = self::getForgroundColor($options);
		$font         = isset($options->font) ? $options->font : $params->image_font_pickadummy;
		$transparency = (isset($options->transparency) ? (int) $options->transparency : (int) $params->image_foreground_transparency);

		$url = 'https://i.pickadummy.com/' . $options->width . 'x' . $options->height;

		self::addToUrl($url, 'greyscale', $greyscale ? 'yes' : '');
		self::addToUrl($url, 'colorize', $colorize);

		self::addToUrl($url, 'dimensions', $dimensions ? 'yes' : '');

		self::addToUrl($url, 'text', $text);
		self::addToUrl($url, 'color', $color == 'ffffff' ? '' : $color);
		self::addToUrl($url, 'font', $font == 'opensans' ? '' : $font);
		self::addToUrl($url, 'transparency', $transparency);

		return $url;
	}

	private static function fakeimg(&$options)
	{
		$params = Params::get();

		$color      = self::getColor($options);
		$text_color = self::getForgroundColor($options);

		$opacity = (isset($options->opacity) ? (int) $options->opacity : (int) $params->image_background_opacity);
		$opacity = $opacity == 100 ? '' : ',' . round($opacity * 2.55);

		$text_opacity = (isset($options->text_opacity) ? (int) $options->text_opacity : (int) $params->image_foreground_opacity);
		$text_opacity = $text_opacity == 100 ? '' : ',' . round($text_opacity * 2.55);

		$text = self::getText($options);

		$font = isset($options->font) ? $options->font : $params->image_font_fakeimg;

		$url = 'https://fakeimg.pl'
			. '/' . $options->width . 'x' . $options->height
			. '/' . $color . $opacity
			. '/' . $text_color . $text_opacity;

		if ($text != '')
		{
			self::addToUrl($url, 'text', $text);
		}

		self::addToUrl($url, 'font', $font);

		return $url;
	}

	private static function placeskull(&$options)
	{
		$params = Params::get();

		$color                    = self::getColor($options);
		$options->show_dimensions = isset($options->show_dimensions) ? $options->show_dimensions : $params->image_show_dimensions;

		$url = 'http://placeskull.com'
			. '/' . $options->width . '/' . $options->height
			. '/' . $color
			. ($options->show_dimensions ? '/' . mt_rand(1, 45) . '/1' : '');

		return $url;
	}

	private static function picsum(&$options)
	{
		$params = Params::get();

		$options->color = isset($options->color) ? $options->color : $params->image_color_scheme;

		$url = 'http://picsum.photos'
			. ($options->color ? '' : '/g')
			. '/' . $options->width . '/' . $options->height
			. '?random';

		return $url;
	}

	private static function placeimg(&$options)
	{
		$params = Params::get();

		$options->category = isset($options->category) ? $options->category : $params->image_category_placeimg;
		$options->color    = isset($options->color) ? $options->color : $params->image_color_scheme2;

		$url = 'https://placeimg.com'
			. '/' . $options->width . '/' . $options->height
			. ($options->category ? '/' . $options->category : '/any')
			. ($options->color == 'color' ? '' : '/' . $options->color);

		return $url;
	}

	private static function placebeard(&$options)
	{
		$params = Params::get();

		$options->color           = isset($options->color) ? $options->color : $params->image_color_scheme;
		$options->show_dimensions = isset($options->show_dimensions) ? $options->show_dimensions : $params->image_show_dimensions;

		$url = 'http://placebeard.it'
			. ($options->color ? '' : '/g')
			. '/' . $options->width . '/' . $options->height
			. ($options->show_dimensions ? '' : '/notag');

		return $url;
	}
}
