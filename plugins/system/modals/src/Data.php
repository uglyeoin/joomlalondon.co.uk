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

use Joomla\CMS\Factory as JFactory;
use RegularLabs\Library\RegEx as RL_RegEx;
use RegularLabs\Library\Uri as RL_Uri;

class Data
{
	static $count_inits = [];

	public static function setDataWidthHeight(&$data, $isexternal)
	{
		self::setDataAxis($data, $isexternal, 'width');
		self::setDataAxis($data, $isexternal, 'height');
	}

	public static function setDataAxis(&$data, $isexternal, $axis = 'width')
	{
		if ( ! empty($data[$axis]))
		{
			return;
		}

		$params = Params::get();

		if ($isexternal)
		{
			$data[$axis] = $params->{'external' . $axis} ?: $params->{$axis} ?: '95%';

			return;
		}

		$data[$axis] = $params->{$axis} ?: $params->{'external' . $axis} ?: '95%';
	}

	public static function setDataOpen(&$data, $attributes = null)
	{
		$value = isset($data['open']) ? $data['open'] : '';

		if (is_bool($value))
		{
			$value = $value ? 'true' : 'false';
		}

		// explode into separate values, so that you can also do: open="1,5,10-20"
		$values = explode(',', $value);

		if ( ! empty($data['openonce']))
		{
			$values[] = 'once';
		}

		if ( ! empty($data['openmin']) || ! empty($data['openmax']))
		{
			$min      = ! empty($data['openmin']) ? (int) $data['openmin'] : 0;
			$max      = ! empty($data['openmax']) ? (int) $data['openmax'] : 0;
			$values[] = $min . '-' . $max;
		}

		$opentype   = ! empty($data['opentype']) ? $data['opentype'] : '';
		$cookie_id  = isset($data['cookie']) ? $data['cookie'] : '';
		$cookie_ttl = isset($data['cookie_ttl']) ? $data['cookie_ttl'] : '';

		unset($data['open']);
		unset($data['openonce']);
		unset($data['openmin']);
		unset($data['openmax']);
		unset($data['opentype']);
		unset($data['cookie']);
		unset($data['cookie_ttl']);

		if (self::isOpen($values, $opentype, $cookie_id, $cookie_ttl))
		{
			$data['open'] = 'true';
		}
	}

	public static function isOpen($values, $opentype, $cookie_id = '', $cookie_ttl = 0)
	{
		if (in_array('true', $values))
		{
			return true;
		}

		if (in_array('false', $values))
		{
			return false;
		}

		if (in_array('once', $values))
		{
			$count = self::getOpenCount($opentype, $cookie_id, $cookie_ttl);

			return $count <= 1;
		}

		$break = false;
		foreach ($values as $value)
		{
			$open = self::getIsOpenFromValue($value, $opentype, $cookie_id, $cookie_ttl);

			if (is_array($open))
			{
				list($open, $break) = $open;
			}

			if ($open)
			{
				return true;
			}

			if ($break)
			{
				return false;
			}
		}

		return false;
	}

	public static function getIsOpenFromValue($value, $opentype, $cookie_id = '', $cookie_ttl = 0)
	{
		// min-max, like: open="2-10"
		if (strpos($value, '-') !== false)
		{
			list($min, $max) = explode('-', $value, 2);
			$min = (int) $min;
			$max = (int) $max;

			$count = self::getOpenCount($opentype, $cookie_id, $cookie_ttl);

			return (($max && $count <= $max) && $count >= $min);
		}

		// single value, like: open="2"
		$open = (int) $value;

		if ($open < 0)
		{
			return false;
		}

		$count = self::getOpenCount($opentype, $cookie_id, $cookie_ttl);

		return (bool) ($count == $open);
	}

	public static function flattenAttributeList($attributes)
	{
		$params = Params::get();

		$string = '';
		foreach ($attributes as $key => $val)
		{
			$key = trim($key);

			// Ignore attributes when key is empty
			if ($key == '')
			{
				continue;
			}

			$val = trim($val);

			// Ignore attributes when value is empty, but not a title or alt attribute
			if ($val == '' && ! in_array($key, ['alt', 'title']))
			{
				continue;
			}

			if (is_bool($val) && in_array($key, $params->booleans))
			{
				$val = $val ? 'true' : 'false';
			}

			$string .= ' ' . $key . '="' . $val . '"';
		}

		return $string;
	}

	public static function flattenDataAttributeList(&$dat)
	{
		if (isset($dat['width']))
		{
			unset($dat['externalWidth']);
		}

		if (isset($dat['height']))
		{
			unset($dat['externalHeight']);
		}

		$data = [];
		foreach ($dat as $key => $val)
		{
			if ( ! $str = self::flattenDataAttribute($key, $val))
			{
				continue;
			}

			$data[] = $str;
		}

		return empty($data) ? '' : ' ' . implode(' ', $data);
	}

	public static function flattenDataAttribute($key, $val)
	{
		if ($key == '')
		{
			return false;
		}

		if (strpos($key, 'title_') !== false || strpos($key, 'description_') !== false)
		{
			return false;
		}

		$key = $key == 'externalWidth' ? 'width' : $key;
		$key = $key == 'externalHeight' ? 'height' : $key;


		$val = str_replace('"', '&quot;', $val);

		if ($key == 'rel')
		{
			// map group value to rel
			return 'rel="' . $val . '"';
		}

		if ($key == 'group')
		{
			// map group value to rel
			return 'data-modal-rel="' . $val . '"';
		}

		if (($key == 'width' || $key == 'height'))
		{
			// set param to innerWidth/innerHeight if width/height is set
			return 'data-modal-inner-' . $key . '="' . $val . '"';
		}

		$params = Params::get();

		if (in_array(strtolower($key), $params->booleans))
		{
			$val = $val ? 'true' : 'false';
		}

		if ($val == '')
		{
			return false;
		}

		if (in_array(strtolower($key), $params->paramNamesLowercase))
		{
			// fix use of lowercase params that should contain uppercase letters
			$key = $params->paramNamesCamelcase[array_search(strtolower($key), $params->paramNamesLowercase)];
			$key = strtolower(RL_RegEx::replace('([A-Z])', '-\1', $key, ''));
		}

		return 'data-modal-' . $key . '="' . $val . '"';
	}

	private static function getOpenCount($type = '', $cookie_id = '', $cookie_ttl = 0)
	{
		$params = Params::get();

		$type = $type ?: $params->open_count_based_on;

		if ($type == 'session')
		{
			return JFactory::getSession()->get('session.counter', 0);
		}

		$cookie_name = 'rl_modals';
		$cookie_name .= ($type == 'page') ? '_' . md5(RL_Uri::get()) : '';
		$cookie_name .= $params->open_cookie_id ? '_' . $params->open_cookie_id : '';
		$cookie_name .= $cookie_id != '' ? '_' . $cookie_id : '';

		$count = (int) isset($_COOKIE[$cookie_name]) ? $_COOKIE[$cookie_name] : 0;

		if (in_array($cookie_name, self::$count_inits))
		{
			return $count;
		}

		$count++;
		$ttl = $cookie_ttl ?: ($params->open_count_ttl ?: (365 * 24 * 60)); // default: 1 year
		$ttl = $ttl * 60;

		JFactory::getApplication()->input->cookie->set($cookie_name, $count, time() + $ttl, '/');

		self::$count_inits[] = $cookie_name;

		return $count;
	}
}
