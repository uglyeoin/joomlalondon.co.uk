<?php
/**
 * @version     1.8.x
 * @package     SocialConnect
 * @author      JoomlaWorks http://www.joomlaworks.net
 * @copyright   Copyright (c) 2006 - 2016 JoomlaWorks Ltd. All rights reserved.
 * @license     http://www.joomlaworks.net/license
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die ;

jimport('joomla.plugin.plugin');
jimport('joomla.user.helper');

class plgAuthenticationSocialConnectTumblr extends JPlugin
{

	function plgAuthenticationSocialConnectTumblr(&$subject, $config)
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

		// Get params
		$params = JComponentHelper::getParams('com_socialconnect');

		// Init login status and user data
		$response->status = version_compare(JVERSION, '3.0', 'ge') ? JAuthentication::STATUS_FAILURE : JAUTHENTICATE_STATUS_FAILURE;
		$data = false;

		// Check for Twitter tokens
		$session = JFactory::getSession();
		$socialConnectTumblrOauthToken = $session->get('socialConnectTumblrOauthToken');
		$socialConnectTumblrOauthTokenSecret = $session->get('socialConnectTumblrOauthTokenSecret');
		$socialConnectServive = $session->get('socialConnectService');

		if ($socialConnectTumblrOauthToken && $socialConnectTumblrOauthTokenSecret && $socialConnectServive == 'tumblr')
		{
			// Load library
			JLoader::register('tmhOAuth', JPATH_SITE.'/components/com_socialconnect/lib/tmhOAuth.php');
			$consumerKey = $params->get('tumblrConsumerKey');
			$consumerSecret = $params->get('tumblrConsumerSecret');
			$tmhOAuth = new tmhOAuth( array(
				'consumer_key' => $consumerKey,
				'consumer_secret' => $consumerSecret,
				'user_token' => $socialConnectTumblrOauthToken,
				'user_secret' => $socialConnectTumblrOauthTokenSecret,
				'host' => 'api.tumblr.com'
			));
			$tmhOAuth->request('GET', $tmhOAuth->url('v2/user/info', ''));

			$data = json_decode($tmhOAuth->response['response']);
			if (isset($data->response->user->name))
			{
				$data->name = $data->response->user->name;
			}
			$data->type = 'tumblr';
		}

		// Set response and session data on success
		if ($data && isset($data->meta) && $data->meta->status == 200)
		{

			// Try to detect existing user from email or possible username values
			$account = SocialConnectHelper::getUserAccount('tumblr', $data->name, $data->name.'@tumblr', $data->name);

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
				$response->type = 'SocialConnect - Tumblr';

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
					$email = $data->name.'@tumblr';
					$response->username = SocialConnectHelper::generateUsername('tumblr', $data->name, $email, $data->name);
					$response->fullname = $data->name;
					$response->email = $email;
					$response->password = '';
				}
			}
		}
	}

}
