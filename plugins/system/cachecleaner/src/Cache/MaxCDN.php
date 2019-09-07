<?php
/**
 * @package         Cache Cleaner
 * @version         7.1.0PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2019 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\CacheCleaner\Cache;

defined('_JEXEC') or die;


use Joomla\CMS\Language\Text as JText;
use NetDNA as ApiNetDNA;
use RegularLabs\Plugin\System\CacheCleaner\Params;

class MaxCDN extends Cache
{
	public static function purge()
	{
		$params = Params::get();

		if (empty($params->maxcdn_authorization_key))
		{
			self::addError(JText::sprintf('CC_ERROR_CDN_NO_AUTHORIZATION_KEY', JText::_('CC_MAXCDN')));

			return -1;
		}

		if (empty($params->maxcdn_zones))
		{
			self::addError(JText::sprintf('CC_ERROR_CDN_NO_ZONES', JText::_('CC_MAXCDN')));

			return -1;
		}

		$api = self::getAPI();
		if ( ! $api || is_string($api))
		{
			self::addError(JText::sprintf('CC_ERROR_CDN_COULD_NOT_INITIATE_API', JText::_('CC_MAXCDN')));
			if (is_string($api))
			{
				self::addError($api);
			}

			return false;
		}

		if ( ! $api = self::getAPI())
		{
			self::addError(JText::sprintf('CC_ERROR_CDN_COULD_NOT_INITIATE_API', JText::_('CC_MAXCDN')));

			return false;
		}

		$zones = explode(',', $params->maxcdn_zones);

		foreach ($zones as $zone)
		{
			$api_call = json_decode($api->delete('/zones/pull.json/' . $zone . '/cache'));

			if ( ! is_null($api_call) && isset($api_call->code) && ($api_call->code == 200 || $api_call->code == 201))
			{
				continue;
			}

			self::addError(JText::sprintf('CC_ERROR_CDN_COULD_NOT_PURGE_ZONE', JText::_('CC_MAXCDN'), $zone));

			return false;
		}

		return true;
	}

	public static function getAPI()
	{
		$params = Params::get();

		$keys = explode('+', $params->maxcdn_authorization_key, 3);

		if (count($keys) < 3)
		{
			return false;
		}

		list($alias, $consumer_key, $consumer_secret) = $keys;

		require_once __DIR__ . '/../Api/NetDNA.php';

		return new ApiNetDNA(trim($alias), trim($consumer_key), trim($consumer_secret));
	}
}
