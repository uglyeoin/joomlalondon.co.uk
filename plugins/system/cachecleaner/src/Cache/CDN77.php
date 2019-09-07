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


use CDN77 as ApiCDN77;
use Joomla\CMS\Language\Text as JText;
use RegularLabs\Plugin\System\CacheCleaner\Params;

class CDN77 extends Cache
{
	public static function purge()
	{
		$params = Params::get();

		if (empty($params->cdn77_login))
		{
			self::addError(JText::sprintf('CC_ERROR_CDN_NO_USERNAME', JText::_('CC_CDN77')));

			return -1;
		}

		if (empty($params->cdn77_passwd))
		{
			self::addError(JText::sprintf('CC_ERROR_CDN_NO_PASSWORD', JText::_('CC_CDN77')));

			return -1;
		}

		if (empty($params->cdn77_ids))
		{
			self::addError(JText::sprintf('CC_ERROR_CDN_NO_IDS', JText::_('CC_CDN77')));

			return -1;
		}

		$api = self::getAPI();
		if ( ! $api || is_string($api))
		{
			self::addError(JText::sprintf('CC_ERROR_CDN_COULD_NOT_INITIATE_API', JText::_('CC_CDN77')));
			if (is_string($api))
			{
				self::addError($api);
			}

			return false;
		}

		$ids = explode(',', $params->cdn77_ids);

		foreach ($ids as $id)
		{
			$api_call = json_decode($api->purge($id));

			if ( ! is_null($api_call) && isset($api_call->status) && $api_call->status == 'ok')
			{
				continue;
			}

			self::addError(JText::sprintf('CC_ERROR_CDN_COULD_NOT_PURGE_ID', JText::_('CC_CDN77'), $id));

			if ( ! empty($api_call->description))
			{
				self::addError(JText::_('CC_CDN77') . ' Error: ' . $api_call->description);
			}

			return false;
		}

		if ( ! empty($api_call->description))
		{
			self::setMessage($api_call->description);
		}

		return true;
	}

	public static function getAPI()
	{
		$params = Params::get();

		require_once __DIR__ . '/../Api/CDN77.php';

		return new ApiCDN77(trim($params->cdn77_login), trim($params->cdn77_passwd));
	}
}
