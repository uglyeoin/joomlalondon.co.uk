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

class SocialConnectHelper
{

	public static function setUserData(&$user)
	{
		$session = JFactory::getSession();
		if (!$user->guest)
		{
			if ($session->get('socialConnectData'))
			{
				$user->socialConnectData = $session->get('socialConnectData');
			}
			else
			{
				$user->socialConnectData = new stdClass;
				$user->socialConnectData->image = '';
				$user->socialConnectData->type = '';
			}
			if (JPluginHelper::isEnabled('user', 'k2'))
			{
				$user->socialConnectData->image = self::getK2Avatar($user);
			}
			if (!isset($user->socialConnectData->image) || empty($user->socialConnectData->image))
			{
				$user->socialConnectData->image = '//www.gravatar.com/avatar/'.md5($user->email).'?s=80&d='.urlencode(JURI::root().'media/socialconnect/images/avatar.jpg');
			}
		}
	}

	public static function setVariables($params)
	{
		$session = JFactory::getSession();
		$user = JFactory::getUser();
		$variables = array();
		$variables['returnURL'] = self::getReturnURL($params);
		$session->set('socialConnectReturn', base64_decode($variables['returnURL']));
		if (version_compare(JVERSION, '1.6.0', 'ge'))
		{
			$variables['option'] = 'com_users';
			$variables['task'] = ($user->guest) ? 'user.login' : 'user.logout';
			$variables['resetPasswordLink'] = JRoute::_('index.php?option=com_users&view=reset');
			$variables['remindUsernameLink'] = JRoute::_('index.php?option=com_users&view=remind');
			$variables['registrationLink'] = JRoute::_('index.php?option=com_users&view=registration');
			$variables['passwordFieldName'] = 'password';
		}
		else
		{
			$variables['option'] = 'com_user';
			$variables['task'] = ($user->guest) ? 'login' : 'logout';
			$variables['resetPasswordLink'] = JRoute::_('index.php?option=com_user&view=reset');
			$variables['remindUsernameLink'] = JRoute::_('index.php?option=com_user&view=remind');
			$variables['registrationLink'] = JRoute::_('index.php?option=com_user&view=register');
			$variables['passwordFieldName'] = 'passwd';
		}
		$variables['introductionMessage'] = ($params->get('introductionMessage') == 'custom') ? $params->get('customIntroductionMessage') : JText::_('JW_SC_LOGIN_INTRODUCTION_MESSAGE_VALUE');
		$variables['registrationMessage'] = ($params->get('registrationMessage') == 'custom') ? $params->get('customRegistrationMessage') : JText::_('JW_SC_LOGIN_REGISTRATION_MESSAGE_VALUE');
		$variables['signInMessage'] = ($params->get('signInMessage') == 'custom') ? $params->get('customSignInMessage') : JText::_('JW_SC_LOGIN_SIGN_IN_MESSAGE_VALUE');
		$variables['footerMessage'] = ($params->get('footerMessage') == 'custom') ? $params->get('customFooterMessage') : JText::_('JW_SC_LOGIN_FOOTER_MESSAGE_VALUE');
		$variables['rememberMe'] = JPluginHelper::isEnabled('system', 'remember');
		$variables['facebook'] = JPluginHelper::isEnabled('authentication', 'socialconnectfacebook') && $params->get('facebookApplicationId') && $params->get('facebookApplicationSecret');
		$variables['twitter'] = JPluginHelper::isEnabled('authentication', 'socialconnecttwitter') && $params->get('twitterConsumerKey') && $params->get('twitterConsumerSecret');
		$variables['google'] = JPluginHelper::isEnabled('authentication', 'socialconnectgoogle') && $params->get('googleClientId') && $params->get('googleClientSecret') && ($params->get('googleAuthType') == 'google' || $params->get('googleAuthType') == 'both');
		$variables['googlePlus'] = JPluginHelper::isEnabled('authentication', 'socialconnectgoogle') && $params->get('googleClientId') && $params->get('googleClientSecret') && ($params->get('googleAuthType') == 'googlePlus' || $params->get('googleAuthType') == 'both');
		$variables['linkedin'] = JPluginHelper::isEnabled('authentication', 'socialconnectlinkedin') && $params->get('linkedInApiKey') && $params->get('linkedInApiSecret');
		$variables['github'] = JPluginHelper::isEnabled('authentication', 'socialconnectgithub') && $params->get('githubClientId') && $params->get('githubClientSecret');
		$variables['wordpress'] = JPluginHelper::isEnabled('authentication', 'socialconnectwordpress') && $params->get('wpClientId') && $params->get('wpClientSecret');
		$variables['windows'] = JPluginHelper::isEnabled('authentication', 'socialconnectwindows') && $params->get('winClientId') && $params->get('winClientSecret');
		$variables['disqus'] = JPluginHelper::isEnabled('authentication', 'socialconnectdisqus') && $params->get('disqusApiKey') && $params->get('disqusApiSecret');
		$variables['foursquare'] = JPluginHelper::isEnabled('authentication', 'socialconnectfoursquare') && $params->get('foursquareClientId') && $params->get('foursquareClientSecret');
		$variables['instagram'] = JPluginHelper::isEnabled('authentication', 'socialconnectinstagram') && $params->get('instagramClientId') && $params->get('instagramClientSecret');
		$variables['soundcloud'] = JPluginHelper::isEnabled('authentication', 'socialconnectsoundcloud') && $params->get('scClientId') && $params->get('scClientSecret');
		$variables['stackexchange'] = JPluginHelper::isEnabled('authentication', 'socialconnectstackexchange') && $params->get('seClientId') && $params->get('seClientSecret') && $params->get('seKey') && $params->get('seSite');
		$variables['amazon'] = JPluginHelper::isEnabled('authentication', 'socialconnectamazon') && $params->get('amazonClientId') && $params->get('amazonClientSecret');
		//$variables['yahoo'] = JPluginHelper::isEnabled('authentication', 'socialconnectyahoo') && $params->get('yahooConsumerKey') && $params->get('yahooConsumerSecret');
		$variables['yahoo'] = false;
		$variables['tumblr'] = JPluginHelper::isEnabled('authentication', 'socialconnecttumblr') && $params->get('tumblrConsumerKey') && $params->get('tumblrConsumerSecret');
		$variables['services'] = ($variables['yahoo'] || $variables['facebook'] || $variables['twitter'] || $variables['google'] || $variables['linkedin'] || $variables['github'] || $variables['wordpress'] || $variables['windows'] || $variables['disqus'] || $variables['foursquare'] || $variables['instagram'] || $variables['soundcloud'] || $variables['amazon'] || $variables['stackexchange'] || $variables['tumblr']);
		$variables['facebookLink'] = JRoute::_('index.php?option=com_socialconnect&view=login&task=facebookOauth&return='.$variables['returnURL']);
		$variables['twitterLink'] = JRoute::_('index.php?option=com_socialconnect&view=login&task=twitterOauth&return='.$variables['returnURL']);
		$variables['googleLink'] = JRoute::_('index.php?option=com_socialconnect&view=login&task=googleOauth&return='.$variables['returnURL']);
		$variables['googlePlusLink'] = JRoute::_('index.php?option=com_socialconnect&view=login&task=googlePlusOauth&return='.$variables['returnURL']);
		$variables['linkedinLink'] = JRoute::_('index.php?option=com_socialconnect&view=login&task=linkedInOauth&return='.$variables['returnURL']);
		$variables['githubLink'] = JRoute::_('index.php?option=com_socialconnect&view=login&task=githubOauth&return='.$variables['returnURL']);
		$variables['wordpressLink'] = JRoute::_('index.php?option=com_socialconnect&view=login&task=wordpressOauth&return='.$variables['returnURL']);
		$variables['windowsLink'] = JRoute::_('index.php?option=com_socialconnect&view=login&task=windowsOauth&return='.$variables['returnURL']);
		$variables['stackExchangeLink'] = JRoute::_('index.php?option=com_socialconnect&view=login&task=stackExchangeOauth&return='.$variables['returnURL']);
		$variables['disqusLink'] = JRoute::_('index.php?option=com_socialconnect&view=login&task=disqusOauth&return='.$variables['returnURL']);
		$variables['soundcloudLink'] = JRoute::_('index.php?option=com_socialconnect&view=login&task=soundCloudOauth&return='.$variables['returnURL']);
		$variables['instagramLink'] = JRoute::_('index.php?option=com_socialconnect&view=login&task=instagramOauth&return='.$variables['returnURL']);
		$variables['foursquareLink'] = JRoute::_('index.php?option=com_socialconnect&view=login&task=foursquareOauth&return='.$variables['returnURL']);
		$variables['amazonLink'] = JRoute::_('index.php?option=com_socialconnect&view=login&task=amazonOauth&return='.$variables['returnURL']);
		$variables['yahooLink'] = JRoute::_('index.php?option=com_socialconnect&view=login&task=yahooOauth&return='.$variables['returnURL']);
		$variables['tumblrLink'] = JRoute::_('index.php?option=com_socialconnect&view=login&task=tumblrOauth&return='.$variables['returnURL']);
		$variables['usernameLabel'] = (JPluginHelper::isEnabled('authentication', 'socialconnectemail')) ? JText::_('JW_SC_USERNAME_OR_EMAIL') : JText::_('JW_SC_USERNAME');
		$variables['menu'] = SocialConnectHelper::getMenu($params);
		$variables['K2Menu'] = SocialConnectHelper::getK2Menu($params);
		$variables['accountLink'] = JRoute::_((version_compare(JVERSION, '1.6.0', 'ge')) ? 'index.php?option=com_users&view=profile&layout=edit' : 'index.php?option=com_user&view=user&task=edit');
		$variables['moduleClassSuffix'] = $params->get('moduleclass_sfx');
		$variables['ning'] = false;
		$variables['persona'] = false;
		return $variables;
	}

	public static function loadHeadData(&$params, $type = 'module')
	{

		jimport('joomla.filesystem.file');
		$mainframe = JFactory::getApplication();
		$user = JFactory::getUser();
		$document = JFactory::getDocument();
		$template = $params->get('template', 'default');
		if ($type == 'component')
		{
			if (JFile::exists(JPATH_SITE.'/templates/'.$mainframe->getTemplate().'/html/com_socialconnect/'.$template.'/css/style.css'))
			{
				$document->addStylesheet(JURI::root(true).'/templates/'.$mainframe->getTemplate().'/html/com_socialconnect/'.$template.'/css/style.css?v=1.8.0');
			}
			else
			{
				$document->addStylesheet(JURI::root(true).'/components/com_socialconnect/templates/'.$template.'/css/style.css?v=1.8.0');
			}
		}
		else
		{
			if (JFile::exists(JPATH_SITE.'/templates/'.$mainframe->getTemplate().'/html/mod_socialconnect/'.$template.'/css/style.css'))
			{
				$document->addStylesheet(JURI::root(true).'/templates/'.$mainframe->getTemplate().'/html/mod_socialconnect/'.$template.'/css/style.css?v=1.8.0');
			}
			else
			{
				$document->addStylesheet(JURI::root(true).'/modules/mod_socialconnect/tmpl/'.$template.'/css/style.css?v=1.8.0');
			}
		}
		if (version_compare(JVERSION, '1.6.0', 'ge'))
		{
			$componentParams = JComponentHelper::getParams('com_socialconnect');
		}
		else
		{
			$component = JComponentHelper::getComponent('com_socialconnect');
			$componentParams = new JParameter($component->params);
		}
		$params->merge($componentParams);
		$usersConfig = JComponentHelper::getParams('com_users');
		$params->set('allowUserRegistration', $usersConfig->get('allowUserRegistration'));

		// SocialConnect JS
		if (version_compare(JVERSION, '3.0.0', 'ge'))
		{
			JHtml::_('jquery.framework');
		}
		$document->addScript(JURI::root(true).'/components/com_socialconnect/js/socialconnect.js?v=1.8.0');

	}

	public static function loadModuleCSS($module, $path)
	{
		$mainframe = JFactory::getApplication();
		$document = JFactory::getDocument();
		if ($module && $path && $document->getType() == 'html')
		{
			if (JFile::exists(JPATH_SITE.'/templates/'.$mainframe->getTemplate().'/html/'.$module.'/'.$path))
			{
				$document->addStylesheet(JURI::root(true).'/templates/'.$mainframe->getTemplate().'/html/'.$module.'/'.$path);
			}
			else
			{
				$document->addStylesheet(JURI::root(true).'/modules/'.$module.'/tmpl/'.$path);
			}
		}
	}

	public static function getReturnURL($params)
	{
		$application = JFactory::getApplication();
		$user = JFactory::getUser();
		$type = ($user->guest) ? 'login' : 'logout';
		$return = JRequest::getVar('return', '', 'method', 'base64');
		if ($type == 'login')
		{
			if ($return)
			{
				return $return;
			}
			else
			{
				if (version_compare(JVERSION, '2.5', 'ge'))
				{
					$data = $application->getUserState('users.login.form.data', array());
					if (isset($data['return']) && $data['return'])
					{
						return base64_encode($data['return']);
					}
				}
			}

		}
		if ($itemid = $params->get($type))
		{
			$mainframe = JFactory::getApplication();
			$menu = $mainframe->getMenu();
			$item = $menu->getItem($itemid);
			if ($item)
			{
        if (version_compare(JVERSION, '2.5', 'ge'))
        {
          $url = 'index.php?Itemid='.$item->id;
        }
        else
        {
          $url = JRoute::_('index.php?Itemid='.$itemid, false);
        }
			}
			else
			{
				$uri = JFactory::getURI();
				$url = $uri->toString();
			}
		}
		else
		{
			$uri = JFactory::getURI();
			$url = $uri->toString();
		}
		return base64_encode($url);
	}

	public static function getMenu($params)
	{
		$user = JFactory::getUser();
		$mainframe = JFactory::getApplication();
		$menu = $mainframe->getMenu();
		$links = array();
		if ($user->guest || !$params->get('menutype'))
		{
			return $links;
		}
		if (version_compare(JVERSION, '1.6.0', 'ge'))
		{
			require_once (JPATH_SITE.'/modules/mod_menu/helper.php');
			$params->set('showAllChildren', 1);
			$links = modMenuHelper::getList($params);
		}
		else
		{
			$links = $menu->getItems('menutype', $params->get('menutype'));
		}

		$active = $menu->getActive();
		$activeID = isset($active) ? $active->id : $menu->getDefault()->id;
		$path = isset($active) ? $active->tree : array();
		$popUpOptions = $options = 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,'.$params->get('window_open');
		foreach ($links as $link)
		{

			if (version_compare(JVERSION, '1.6.0', 'ge'))
			{
				$link->href = $link->flink;
			}
			else
			{
				$link->title = $link->name;
				$link->level = $link->sublevel;
				switch ($link->type)
				{
					case 'separator' :
						continue;
						break;

					case 'url' :
						if ((strpos($link->link, 'index.php?') === 0) && (strpos($link->link, 'Itemid=') === false))
						{
							$link->url = $link->link.'&amp;Itemid='.$link->id;
						}
						else
						{
							$link->url = $link->link;
						}
						break;

					default :
						$router = JSite::getRouter();
						$link->url = $router->getMode() == JROUTER_MODE_SEF ? 'index.php?Itemid='.$link->id : $link->link.'&Itemid='.$link->id;
						break;
				}

				$iParams = version_compare(JVERSION, '1.6.0', 'ge') ? new JRegistry($link->params) : new JParameter($link->params);
				$iSecure = $iParams->def('secure', 0);
				if ($link->home == 1)
				{
					$link->url = JURI::base();
				}
				elseif (strcasecmp(substr($link->url, 0, 4), 'http') && (strpos($link->link, 'index.php?') !== false))
				{
					$link->url = JRoute::_($link->url, true, $iSecure);
				}
				else
				{
					$link->url = str_replace('&', '&amp;', $link->url);
				}
				$link->href = $link->url;
			}

			// Build the class attribute
			$link->class = 'item-'.$link->id;
			if ($link->id == $activeID)
			{
				$link->class .= ' current';
			}
			if (in_array($link->id, $path))
			{
				$link->class .= ' active';
			}
			elseif ($link->type == 'alias')
			{
				$aliasToId = $link->params->get('aliasoptions');
				if (count($path) > 0 && $aliasToId == $path[count($path) - 1])
				{
					$link->class .= ' active';
				}
				elseif (in_array($aliasToId, $path))
				{
					$link->class .= ' alias-parent-active';
				}
			}
			if (isset($link->deeper) && $link->deeper)
			{
				$link->class .= ' deeper';
			}
			if ($link->parent)
			{
				$link->class .= ' parent';
			}
			if (!empty($class))
			{
				$link->class = trim($link->class);
			}

		}
		return $links;
	}

	public static function getK2Menu()
	{
		jimport('joomla.filesystem.file');
		$user = JFactory::getUser();
		$links = array();
		if ($user->guest || !JFile::exists(JPATH_SITE.'/components/com_k2/k2.php'))
		{
			return $links;
		}
		JHTML::_('behavior.modal');
		require_once (JPATH_SITE.'/components/com_k2/helpers/utilities.php');
		require_once (JPATH_SITE.'/components/com_k2/helpers/permissions.php');
		if (JRequest::getCmd('option') != 'com_k2')
		{
			K2HelperPermissions::setPermissions();
		}
		if (K2HelperPermissions::canAddItem())
		{
			$links['add'] = JRoute::_('index.php?option=com_k2&view=item&task=add&tmpl=component');
		}
		require_once (JPATH_SITE.'/components/com_k2/helpers/route.php');
		$links['user'] = JRoute::_(K2HelperRoute::getUserRoute($user->id));
		$links['comments'] = JRoute::_('index.php?option=com_k2&view=comments&tmpl=component');
		return $links;
	}

	public static function getK2Avatar($user)
	{
		$avatar = null;
		$db = JFactory::getDBO();
		$query = "SELECT id FROM #__k2_users WHERE userID = ".(int)$user->id;
		$db->setQuery($query);
		$K2UserID = $db->loadResult();
		if ($K2UserID)
		{
			JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/tables');
			$row = JTable::getInstance('K2User', 'Table');
			$row->load($K2UserID);
			if ($row->image)
			{
				$avatar = JURI::root(true).'/media/k2/users/'.$row->image;
			}
		}
		return $avatar;
	}

	public static function request($url, $parameters = array(), $method = 'get', $options = array(), $information = false, $silent = false)
	{
		// Get application
		$application = JFactory::getApplication();

		// Make method lowercase
		$method = JString::strtolower($method);

		// Initialize the cURL handler
		$ch = curl_init();

		// If the method is GET apply the parameters to URL
		if ($method == 'get' && count($parameters))
		{
			$url .= '?'.http_build_query($parameters, '', '&');
		}

		// Set default cURL options
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_CAINFO, JPATH_SITE.'/components/com_socialconnect/lib/cacert.pem');
		curl_setopt($ch, CURLOPT_USERAGENT, 'SocialConnect');

		// If method is POST add the parameters
		if ($method == 'post')
		{
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters, '', '&'));
		}

		// Add any other options
		foreach ($options as $option => $value)
		{
			curl_setopt($ch, $option, $value);
		}

		// Add the connect timeout and timeout options from component parameters
		$params = JComponentHelper::getParams('com_socialconnect');
		$curlConnectTimeout = $params->get('curlConnectTimeout', null);
		$curlTimeout = $params->get('curlTimeout', null);
		if (is_numeric($curlTimeout))
		{
			curl_setopt($ch, CURLOPT_TIMEOUT, $curlTimeout);
		}
		if (is_numeric($curlConnectTimeout))
		{
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $curlConnectTimeout);
		}

		// Get result
		$result = curl_exec($ch);

		// Get
		$info = curl_getinfo($ch);

		// Get the effective URL and add it to the information array
		$effectiveURL = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
		$info['effective_url'] = $effectiveURL;

		// cURL failed, show the error
		if ($result === false)
		{
			$application->enqueueMessage(curl_error($ch), 'error');
		}
		// cURL executed but the response is not 200, show the response along with the error
		else if (!$silent && $info['http_code'] != 200 && $info['http_code'] != 201)
		{
			$application->enqueueMessage($info['http_code'].': '.strip_tags($result), 'error');
		}
		// Close the cURL handler
		curl_close($ch);

		// Return an object with the result and the information or just the result
		if ($information)
		{
			$response = new stdClass;
			$response->result = $result;
			$response->information = $info;
			return $response;
		}
		else
		{
			return $result;
		}

	}

	public static function verify()
	{
		$application = JFactory::getApplication();
		$session = JFactory::getSession();
		$params = JComponentHelper::getParams('com_socialconnect');
		$application->login(array('username' => '', 'password' => ''));
		$user = JFactory::getUser();
		if (!$user->guest)
		{
			$data = $session->get('socialConnectData');
			if ($data)
			{
				if (($data->type == 'twitter' && $user->email == $data->screen_name.'@twitter') || ($data->type == 'stackexchange' && $user->email == $data->account_id.'@stackexchange') || ($data->type == 'soundcloud' && $user->email == $data->id.'@soundcloud') || ($data->type == 'instagram' && $user->email == $data->id.'@instagram') || ($data->type == 'yahoo' && $user->email == $data->guid.'@yahoo') || ($data->type == 'tumblr' && $user->email == $data->name.'@tumblr'))
				{
					$application->redirect(JRoute::_('index.php?option=com_socialconnect&view=login&task=email'));
				}
			}
		}

		$returnURL = $session->get('socialConnectReturn');
		if (!$returnURL)
		{
			$application->redirect(JURI::root());
		}
		else
		{
			if (version_compare(JVERSION, '2.5', 'ge'))
			{
				$returnURL = JRoute::_($returnURL);
			}
			$application->redirect($returnURL);
		}

	}

	public static function signOut()
	{
		$mainframe = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_socialconnect');
		$session = JFactory::getSession();
		$session->clear('socialConnectData');
		$session->clear('oauth');
		$session->clear('access_token');
		$mainframe->logout();
		$returnURL = JRequest::getInt('return');
		if (!$returnURL)
		{
			$returnURL = $params->get('logout');
		}
		$menu = JSite::getMenu();
		$item = $menu->getItem($returnURL);
		if ($item)
		{
			$mainframe->redirect(JRoute::_('index.php?Itemid='.$item->id, false));
		}
		else
		{
			$mainframe->redirect(JURI::root());
		}
		$mainframe->redirect(JURI::root());
	}

	public static function getUserAccount($service, $id, $email, $name)
	{
		$usernames = array();
		$usernames[] = self::generateUsername($service, $id, $email, $name, 'id');
		//$usernames[] = self::generateUsername($service, $id, $email, $name, 'name');
		if ($email)
		{
			$usernames[] = self::generateUsername($service, $id, $email, $name, 'email');
			$usernames[] = self::generateUsername($service, $id, $email, $name, 'hash');
		}
		$db = JFactory::getDBO();
		$query = "SELECT id FROM #__users WHERE email = ".$db->quote($email);
		foreach ($usernames as $username)
		{
			$query .= " OR username = ".$db->quote($username);
		}
		$db->setQuery($query);
		$id = $db->loadResult();
		if ($id)
		{
			return JFactory::getUser($id);
		}
		else
		{
			return false;
		}
	}

	public static function canLogin($account)
	{
		$result = true;
		$usersConfig = JComponentHelper::getParams('com_users');
		if (!$usersConfig->get('allowUserRegistration') && !$account)
		{
			$result = false;
		}
		return $result;
	}

	public static function generateUsername($service, $id, $email, $name, $mode = null)
	{
		if (is_null($mode))
		{
			$params = JComponentHelper::getParams('com_socialconnect');
			$mode = $params->get('usernameGeneration', 'id');
		}
		switch($mode)
		{
			default :
			case 'id' :
				$username = $id.'@'.$service;
				break;
			case 'name' :
				$username = $name;
				break;
			case 'email' :
				$username = $email;
				break;
			case 'hash' :
				$username = md5($email);
				break;
		}
		return $username;
	}

}
