<?php
/**
 * @version		$Id$
 * @package		SocialConnect
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2016 JoomlaWorks Ltd. All rights reserved.
 * @license		http://www.joomlaworks.net/license
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die ;

jimport('joomla.plugin.plugin');

class plgAuthenticationSocialConnectStackExchange extends JPlugin
{

	function plgAuthenticationSocialConnectStackExchange(&$subject, $config)
	{
		parent::__construct($subject, $config);
	}

	function onUserAuthenticate($credentials, $options, &$response)
	{
		$this->onAuthenticate($credentials, $options, $response);
	}

	function onAuthenticate($credentials, $options, &$response)
	{

		// Front-end only
		$application = JFactory::getApplication();
		if ($application->isAdmin())
		{
			return false;
		}

		// Init login status and user data
		$response->status = version_compare(JVERSION, '3.0', 'ge') ? JAuthentication::STATUS_FAILURE : JAUTHENTICATE_STATUS_FAILURE;
		$data = false;

		// Check for access token
		$session = JFactory::getSession();
		$socialConnectStackExchangeAccessToken = $session->get('socialConnectStackExchangeAccessToken');
		$socialConnectServive = $session->get('socialConnectService');

		if ($socialConnectStackExchangeAccessToken && $socialConnectServive == 'stackexchange')
		{
			$params = JComponentHelper::getParams('com_socialconnect');
			$parameters = array('access_token' => $socialConnectStackExchangeAccessToken, 'key' => $params->get('seKey'), 'site' => $params->get('seSite'));
			JLoader::register('SocialConnectHelper', JPATH_SITE.'/components/com_socialconnect/helpers/socialconnect.php');
			$result = SocialConnectHelper::request('https://api.stackexchange.com/2.2/me', $parameters, 'GET', array(CURLOPT_ENCODING => 'deflate'));
			$result = json_decode($result);
			if (isset($result->items) && isset($result->items[0]))
			{
				$data = $result->items[0];
				$data->image = $data->profile_image;
				if (isset($data->about_me))
				{
					$data->description = $data->about_me;
				}
				$data->type = 'stackexchange';
			}
		}

		// Set response and session data on success
		if ($data)
		{
			// Try to detect existing user from email or possible username values
			$account = SocialConnectHelper::getUserAccount('stackexchange', $data->account_id, $data->account_id.'@stackexchange', $data->display_name);

			// If registrations are disabled do not allow login by new users
			if (!SocialConnectHelper::canLogin($account))
			{
				$response->error_message = JText::_('JW_SC_USERS_REGISTRATION_IS_CURRENTLY_DISABLED');
			}
			else
			{
				// Store network profile data to session
				$session->set('socialConnectData', $data);

				// Set authentication success
				$response->status = version_compare(JVERSION, '3.0', 'ge') ? JAuthentication::STATUS_SUCCESS : JAUTHENTICATE_STATUS_SUCCESS;

				// Set authentication type
				$response->type = 'SocialConnect - StackExchange';

				// Empty any error messages
				$response->error_message = '';

				// Set the rest response attributes based on the user account
				if ($account)
				{
					$response->username = $account->username;
					$response->fullname = $account->name;
					$response->email = $account->email;
				}
				else
				{
					// Generate username
					$response->username = SocialConnectHelper::generateUsername('stackexchange', $data->account_id, $data->account_id.'@stackexchange', $data->display_name);
					$response->fullname = $data->display_name;
					$response->email = $data->account_id.'@stackexchange';
					$response->password = '';
				}
			}
		}
	}

}
