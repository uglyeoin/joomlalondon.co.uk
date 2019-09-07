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

class plgAuthenticationSocialConnectFoursquare extends JPlugin
{

	function plgAuthenticationSocialConnectFoursquare(&$subject, $config)
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
		$socialConnectFoursquareAccessToken = $session->get('socialConnectFoursquareAccessToken');
		$socialConnectServive = $session->get('socialConnectService');

		if ($socialConnectFoursquareAccessToken && $socialConnectServive == 'foursquare')
		{
			$parameters = array('oauth_token' => $socialConnectFoursquareAccessToken, 'v' => '20140509');
			JLoader::register('SocialConnectHelper', JPATH_SITE.'/components/com_socialconnect/helpers/socialconnect.php');
			$result = SocialConnectHelper::request('https://api.foursquare.com/v2/users/self', $parameters, 'GET');
			$data =  json_decode($result)->response->user;
			$data->image = $data->photo->prefix.'128x128'.$data->photo->suffix;
			$data->type = 'foursquare';
		}

		// Set response and session data on success
		if ($data)
		{

			// Try to detect existing user from email or possible username values
			$account = SocialConnectHelper::getUserAccount('foursquare', $data->id, $data->contact->email, $data->firstName.' '.$data->lastName);

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
				$response->type = 'SocialConnect - Foursquare';

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
					$response->username = SocialConnectHelper::generateUsername('foursquare', $data->id, $data->contact->email, $data->firstName.' '.$data->lastName);
					$response->fullname = $data->firstName.' '.$data->lastName;
					$response->email = $data->contact->email;
					$response->password = '';
				}
			}
		}
	}

}
