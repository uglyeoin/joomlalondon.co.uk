<?php
/**
 * @version     1.8.x
 * @package     SocialConnect
 * @author      JoomlaWorks http://www.joomlaworks.net
 * @copyright   Copyright (c) 2006 - 2016 JoomlaWorks Ltd. All rights reserved.
 * @license     http://www.joomlaworks.net/license
 */

// no direct access
defined('_JEXEC') or die ;

class SocialConnectControllerLogin extends SocialConnectController
{

	public function display($cachable = false, $urlparams = false)
	{
		JRequest::setVar('view', 'login');
		parent::display(false);
	}

	public function twitter()
	{
		$view = $this->getView('login', 'html');
		$view->setLayout('twitter');
		$view->twitter();
		return $this;
	}

	public function email()
	{
		$view = $this->getView('login', 'html');
		$view->setLayout('email');
		$view->email();
		return $this;
	}

	public function facebookOauth()
	{
		return $this->_authorize('Facebook');
	}

	public function googlePlusOauth()
	{
		return $this->_authorize('GooglePlus');
	}

	public function googleOauth()
	{
		return $this->_authorize('Google');
	}

	public function githubOauth()
	{
		return $this->_authorize('Github');
	}

	public function wordpressOauth()
	{
		return $this->_authorize('Wordpress');
	}

	public function windowsOauth()
	{
		return $this->_authorize('Windows');
	}

	public function twitterOauth()
	{
		return $this->_authorize('Twitter');
	}

	public function linkedInOauth()
	{
		return $this->_authorize('LinkedIn');
	}

	public function stackExchangeOauth()
	{
		return $this->_authorize('StackExchange');
	}

	public function disqusOauth()
	{
		return $this->_authorize('DISQUS');
	}

	public function soundCloudOauth()
	{
		return $this->_authorize('SoundCloud');
	}

	public function instagramOauth()
	{
		return $this->_authorize('Instagram');
	}

	public function foursquareOauth()
	{
		return $this->_authorize('Foursquare');
	}

	public function amazonOauth()
	{
		return $this->_authorize('Amazon');
	}

	public function yahooOauth()
	{
		return $this->_authorize('Yahoo');
	}

	public function tumblrOauth()
	{
		return $this->_authorize('Tumblr');
	}

	private function _authorize($service)
	{
		JRequest::setVar('tmpl', 'component');
		$this->setReturn();
		$params = JComponentHelper::getParams('com_socialconnect');
		$user = JFactory::getUser();
		if (!$user->guest)
		{
			$this->addHeadCode();
			return $this;
		}
		require_once JPATH_SITE.'/components/com_socialconnect/helpers/oauth.php';
		SocialConnectOAuthHelper::$service = $service;
		switch($service)
		{
			case 'Facebook' :
				SocialConnectOAuthHelper::$authorization_endpoint = 'https://www.facebook.com/v2.5/dialog/oauth';
				SocialConnectOAuthHelper::$token_endpoint = 'https://graph.facebook.com/v2.5/oauth/access_token';
				SocialConnectOAuthHelper::$redirect_uri = JURI::root(false).'index.php?option=com_socialconnect&view=login&task=facebookoauth';
				SocialConnectOAuthHelper::$client_id = $params->get('facebookApplicationId');
				SocialConnectOAuthHelper::$client_secret = $params->get('facebookApplicationSecret');
				SocialConnectOAuthHelper::$scope = 'email';
				break;
			case 'GooglePlus' :
				SocialConnectOAuthHelper::$authorization_endpoint = 'https://accounts.google.com/o/oauth2/auth';
				SocialConnectOAuthHelper::$token_endpoint = 'https://accounts.google.com/o/oauth2/token';
				SocialConnectOAuthHelper::$redirect_uri = JURI::root(false).'index.php?option=com_socialconnect&view=login&task=googleplusoauth';
				SocialConnectOAuthHelper::$client_id = $params->get('googleClientId');
				SocialConnectOAuthHelper::$client_secret = $params->get('googleClientSecret');
				SocialConnectOAuthHelper::$scope = 'https://www.googleapis.com/auth/plus.login https://www.googleapis.com/auth/userinfo.email';
				break;
			case 'Google' :
				SocialConnectOAuthHelper::$authorization_endpoint = 'https://accounts.google.com/o/oauth2/auth';
				SocialConnectOAuthHelper::$token_endpoint = 'https://accounts.google.com/o/oauth2/token';
				SocialConnectOAuthHelper::$redirect_uri = JURI::root(false).'index.php?option=com_socialconnect&view=login&task=googleoauth';
				SocialConnectOAuthHelper::$client_id = $params->get('googleClientId');
				SocialConnectOAuthHelper::$client_secret = $params->get('googleClientSecret');
				SocialConnectOAuthHelper::$scope = 'https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email';
				break;
			case 'Github' :
				SocialConnectOAuthHelper::$authorization_endpoint = 'https://github.com/login/oauth/authorize';
				SocialConnectOAuthHelper::$token_endpoint = 'https://github.com/login/oauth/access_token';
				SocialConnectOAuthHelper::$redirect_uri = JURI::root(false).'index.php?option=com_socialconnect&view=login&task=githuboauth';
				SocialConnectOAuthHelper::$client_id = $params->get('githubClientId');
				SocialConnectOAuthHelper::$client_secret = $params->get('githubClientSecret');
				SocialConnectOAuthHelper::$scope = 'user:email';
				break;
			case 'Wordpress' :
				SocialConnectOAuthHelper::$authorization_endpoint = 'https://public-api.wordpress.com/oauth2/authorize';
				SocialConnectOAuthHelper::$token_endpoint = 'https://public-api.wordpress.com/oauth2/token';
				SocialConnectOAuthHelper::$redirect_uri = JURI::root(false).'index.php?option=com_socialconnect&view=login&task=wordpressoauth';
				SocialConnectOAuthHelper::$client_id = $params->get('wpClientId');
				SocialConnectOAuthHelper::$client_secret = $params->get('wpClientSecret');
				break;
			case 'Windows' :
				SocialConnectOAuthHelper::$authorization_endpoint = 'https://login.live.com/oauth20_authorize.srf';
				SocialConnectOAuthHelper::$token_endpoint = 'https://login.live.com/oauth20_token.srf';
				$uri = JURI::getInstance();
				$scheme = $uri->getScheme();
				$ssl = $scheme === 'https' ? 1 : -1;
				SocialConnectOAuthHelper::$redirect_uri = JRoute::_('index.php?option=com_socialconnect&view=login&task=windowsoauth&Itemid=', true, $ssl);
				SocialConnectOAuthHelper::$client_id = $params->get('winClientId');
				SocialConnectOAuthHelper::$client_secret = $params->get('winClientSecret');
				SocialConnectOAuthHelper::$scope = 'wl.basic,wl.emails';
				break;
			case 'Twitter' :
				SocialConnectOAuthHelper::$version = '1.0';
				SocialConnectOAuthHelper::$consumer_key = $params->get('twitterConsumerKey');
				SocialConnectOAuthHelper::$consumer_secret = $params->get('twitterConsumerSecret');
				SocialConnectOAuthHelper::$endpoint = 'https://api.twitter.com/oauth/';
				SocialConnectOAuthHelper::$oauth_callback = JURI::root(false).'index.php?option=com_socialconnect&view=login&task=twitteroauth';
				break;
			case 'LinkedIn' :
				SocialConnectOAuthHelper::$authorization_endpoint = 'https://www.linkedin.com/uas/oauth2/authorization';
				SocialConnectOAuthHelper::$token_endpoint = 'https://www.linkedin.com/uas/oauth2/accessToken';
				SocialConnectOAuthHelper::$redirect_uri = JURI::root(false).'index.php?option=com_socialconnect&view=login&task=linkedinoauth';
				SocialConnectOAuthHelper::$client_id = $params->get('linkedInApiKey');
				SocialConnectOAuthHelper::$client_secret = $params->get('linkedInApiSecret');
				SocialConnectOAuthHelper::$scope = 'r_basicprofile r_emailaddress';
				break;
			case 'StackExchange' :
				SocialConnectOAuthHelper::$authorization_endpoint = 'https://stackexchange.com/oauth';
				SocialConnectOAuthHelper::$token_endpoint = 'https://stackexchange.com/oauth/access_token';
				SocialConnectOAuthHelper::$redirect_uri = JURI::root(false).'index.php?option=com_socialconnect&view=login&task=stackexchangeoauth';
				SocialConnectOAuthHelper::$client_id = $params->get('seClientId');
				SocialConnectOAuthHelper::$client_secret = $params->get('seClientSecret');
				SocialConnectOAuthHelper::$scope = '';
				break;
			case 'DISQUS' :
				SocialConnectOAuthHelper::$authorization_endpoint = 'https://disqus.com/api/oauth/2.0/authorize';
				SocialConnectOAuthHelper::$token_endpoint = 'https://disqus.com/api/oauth/2.0/access_token/';
				SocialConnectOAuthHelper::$redirect_uri = JURI::root(false).'index.php?option=com_socialconnect&view=login&task=disqusoauth';
				SocialConnectOAuthHelper::$client_id = $params->get('disqusApiKey');
				SocialConnectOAuthHelper::$client_secret = $params->get('disqusApiSecret');
				SocialConnectOAuthHelper::$scope = 'read,email';
				break;
			case 'SoundCloud' :
				SocialConnectOAuthHelper::$authorization_endpoint = 'https://soundcloud.com/connect';
				SocialConnectOAuthHelper::$token_endpoint = 'https://api.soundcloud.com/oauth2/token';
				$uri = JURI::getInstance();
				$scheme = $uri->getScheme();
				$ssl = $scheme === 'https' ? 1 : -1;
				SocialConnectOAuthHelper::$redirect_uri = JRoute::_('index.php?option=com_socialconnect&view=login&task=soundcloudoauth&Itemid=', true, $ssl);
				SocialConnectOAuthHelper::$client_id = $params->get('scClientId');
				SocialConnectOAuthHelper::$client_secret = $params->get('scClientSecret');
				SocialConnectOAuthHelper::$scope = '';
				break;
			case 'Instagram' :
				SocialConnectOAuthHelper::$authorization_endpoint = 'https://api.instagram.com/oauth/authorize';
				SocialConnectOAuthHelper::$token_endpoint = 'https://api.instagram.com/oauth/access_token';
				SocialConnectOAuthHelper::$redirect_uri = JURI::root(false).'index.php?option=com_socialconnect&view=login&task=instagramoauth';
				SocialConnectOAuthHelper::$client_id = $params->get('instagramClientId');
				SocialConnectOAuthHelper::$client_secret = $params->get('instagramClientSecret');
				SocialConnectOAuthHelper::$scope = 'basic';
				SocialConnectOAuthHelper::$display = '';
				break;
			case 'Foursquare' :
				SocialConnectOAuthHelper::$authorization_endpoint = 'https://foursquare.com/oauth2/authenticate';
				SocialConnectOAuthHelper::$token_endpoint = 'https://foursquare.com/oauth2/access_token';
				SocialConnectOAuthHelper::$redirect_uri = JURI::root(false).'index.php?option=com_socialconnect&view=login&task=foursquareoauth';
				SocialConnectOAuthHelper::$client_id = $params->get('foursquareClientId');
				SocialConnectOAuthHelper::$client_secret = $params->get('foursquareClientSecret');
				SocialConnectOAuthHelper::$scope = '';
				SocialConnectOAuthHelper::$display = '';
				break;
			case 'Amazon' :
				SocialConnectOAuthHelper::$authorization_endpoint = 'https://www.amazon.com/ap/oa';
				SocialConnectOAuthHelper::$token_endpoint = 'https://api.amazon.com/auth/o2/token';
				SocialConnectOAuthHelper::$redirect_uri = JURI::root(false).'index.php?option=com_socialconnect&view=login&task=amazonoauth';
				SocialConnectOAuthHelper::$client_id = $params->get('amazonClientId');
				SocialConnectOAuthHelper::$client_secret = $params->get('amazonClientSecret');
				SocialConnectOAuthHelper::$scope = 'profile';
				break;
			case 'Yahoo' :
				SocialConnectOAuthHelper::$version = '1.0';
				SocialConnectOAuthHelper::$consumer_key = $params->get('yahooConsumerKey');
				SocialConnectOAuthHelper::$consumer_secret = $params->get('yahooConsumerSecret');
				SocialConnectOAuthHelper::$endpoint = 'https://api.login.yahoo.com/oauth/v2/';
				SocialConnectOAuthHelper::$oauth_callback = JURI::root(false).'index.php?option=com_socialconnect&view=login&task=yahoooauth';
				break;
			case 'Tumblr' :
				SocialConnectOAuthHelper::$version = '1.0';
				SocialConnectOAuthHelper::$consumer_key = $params->get('tumblrConsumerKey');
				SocialConnectOAuthHelper::$consumer_secret = $params->get('tumblrConsumerSecret');
				SocialConnectOAuthHelper::$endpoint = 'https://www.tumblr.com/oauth/';
				SocialConnectOAuthHelper::$oauth_callback = JURI::root(false).'index.php?option=com_socialconnect&view=login&task=tumblroauth';
				break;
		}
		$result = SocialConnectOAuthHelper::authorize();

		if ($result)
		{
			// Set session variables
			$session = JFactory::getSession();
			$session->set('socialConnectService', strtolower($service));

			// OAuth 2.0
			$session->set('socialConnect'.$service.'AccessToken', SocialConnectOAuthHelper::$access_token);
			// OAuth 1.0
			$session->set('socialConnect'.$service.'OauthToken', SocialConnectOAuthHelper::$oauth_token);
			$session->set('socialConnect'.$service.'OauthTokenSecret', SocialConnectOAuthHelper::$oauth_token_secret);
			// Twitter only ...
			$session->set('socialConnect'.$service.'UserID', SocialConnectOAuthHelper::$user_id);

			// Redirect parent window to verify URL
			$this->addHeadCode();
		}
		else
		{
			$application = JFactory::getApplication();
			$application->enqueueMessage(SocialConnectOAuthHelper::$error, 'error');
		}
		return $this;
	}

	private function addHeadCode()
	{
		$document = JFactory::getDocument();
		$document->addScriptDeclaration('window.opener.location = "'.JRoute::_('index.php?option=com_socialconnect&task=verify', false).'"; window.close();');
	}

	private function setReturn()
	{
		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			$application = JFactory::getApplication();
			$return = $application->input->get('return', null, 'BASE64');
		}
		else
		{
			$return = JRequest::getVar('return', null, 'GET', 'BASE64');
		}
		if (!is_null($return))
		{
			$session = JFactory::getSession();
			$session->set('socialConnectReturn', base64_decode($return));
		}
	}

}
