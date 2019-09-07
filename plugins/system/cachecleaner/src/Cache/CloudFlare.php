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


use CloudFlare as ApiCloudFlare;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Uri\Uri as JUri;
use RegularLabs\Plugin\System\CacheCleaner\Params;

class CloudFlare extends Cache
{
	public static function purge()
	{
		$params = Params::get();

		if (empty($params->cloudflare_username))
		{
			self::addError(JText::sprintf('CC_ERROR_CDN_NO_USERNAME', JText::_('CC_CLOUDFLARE')));

			return -1;
		}

		if (empty($params->cloudflare_token))
		{
			self::addError(JText::sprintf('CC_ERROR_CDN_NO_API_KEY', JText::_('CC_CLOUDFLARE')));

			return -1;
		}

		$api = self::getAPI();
		if ( ! $api || is_string($api))
		{
			self::addError(JText::sprintf('CC_ERROR_CDN_COULD_NOT_INITIATE_API', JText::_('CC_CLOUDFLARE')));
			if (is_string($api))
			{
				self::addError($api);
			}

			return false;
		}

		if (empty($params->cloudflare_domains))
		{
			$params->cloudflare_domains = JUri::getInstance()->toString(['host']);
		}

		$domains = explode(',', $params->cloudflare_domains);

		$api_call = null;

		foreach ($domains as $domain)
		{
			$api_call = json_decode($api->purge($domain));

			if ( ! is_null($api_call) && ! empty($api_call->success))
			{
				continue;
			}

			self::addError(JText::sprintf('CC_ERROR_CDN_COULD_NOT_PURGE_ZONE', JText::_('CC_CLOUDFLARE'), $domain));

			if ( ! empty($api_call->messages))
			{
				self::addError(JText::_('CC_CLOUDFLARE') . ' Error: ' . implode(', ', $api_call->messages));
			}

			return false;
		}

		if ( ! empty($api_call->messages))
		{
			self::setMessage(implode(', ', $api_call->messages));
		}

		return true;
	}

	public static function getAPI()
	{
		$params = Params::get();

		require_once __DIR__ . '/../Api/CloudFlare.php';

		return new ApiCloudFlare(trim($params->cloudflare_username), trim($params->cloudflare_token));
	}
}
