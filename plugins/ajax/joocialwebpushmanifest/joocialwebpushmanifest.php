<?php

/**
 * @package     Extly.Plugins
 * @subpackage  joocialwebpushmanifest - Joocial plugin to support Webpush notifications for Joomla!
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2018 Extly, CB. All rights reserved.
 * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

defined('_JEXEC') or die;

// Import library dependencies
jimport('joomla.plugin.plugin');

/**
 * plgAjaxJoocialWebpushManifest class.
 *
 * @package     Extly.Components
 * @subpackage  com_autotweet
 * @since       1.0
 */
class PlgAjaxJoocialWebpushManifest extends JPlugin
{
	/**
	 * onAjaxJoocialWebpushManifest
	 *
	 * @return	void
	 */
	public function onAjaxJoocialWebpushManifest()
	{
		$this->params->get('onesignal_custom_notify_button');
		$manifest = array();

		$sitename = JFactory::getConfig()->get('sitename');
		$metaDesc = JFactory::getConfig()->get('MetaDesc');

		if (empty($metaDesc))
		{
			$metaDesc = $sitename;
		}

		$manifest['name'] = $this->params->get('name', $metaDesc);
		$manifest['short_name'] = $this->params->get('short_name', $sitename);

		$pushservice = $this->params->get('pushservice');

		switch ($pushservice)
		{
			case 'onesignal':
				$manifest['start_url'] = '/';
				$manifest['gcm_sender_id'] = '482941778795';
				$manifest['DO_NOT_CHANGE_GCM_SENDER_ID'] = 'Do not change the GCM Sender ID';
				break;

			case 'pushwoosh':
				$manifest['gcm_sender_id'] = $this->params->get('pushwoosh_gcm_sender_id');
				$manifest['gcm_user_visible_only'] = true;
				break;
		}

		$manifest['display'] = 'standalone';

		return json_encode($manifest);
	}
}
