<?php

/**
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

defined('_JEXEC') or die;

require_once JPATH_AUTOTWEET_HELPERS . '/channels/OAuth/OAuth.php';

/**
 * AutotweetControllerMapis
 *
 * @package     Extly.Components
 * @subpackage  com_autotweet
 * @since       1.0
 */
class AutotweetControllerMapis extends ExtlyController
{
	protected $myMethods = array(
		// OAuth API
		'request_token' => 'getRequestToken',
		'access_token' => 'getAccessToken',
		'get_globals' => 'getGlobals',
		'logout' => 'execLogout',
		'ping' => 'checkToken',

		// Stats API
		'requests_chart' => 'getRequestsChartData',
		'posts_chart' => 'getPostsChartData',
		'posts_timeline' => 'getPostsTimeline',

		// Composer API
		'get_menuitems' => 'getMenuitems',
		'get_url' => 'getUrlByItemId',

		// Requests API
		'requests_save_plugin' => 'applyplugin',
		'requests_save_composer' => 'applyComposer',
		'requests_browse' => 'browseRequests',
		'requests_read' => 'readRequest',
		'requests_publish' => 'publishRequest',
		'requests_cancel' => 'cancelRequest',

		// Posts API
		'posts_browse' => 'browsePosts',
		'posts_read' => 'readPost',
		'posts_publish' => 'publishPost',
		'posts_cancel' => 'cancelPost',

		// Images API
		'images_save' => 'uploadFile'
	);

	protected $stats = null;

	/**
	 * execute.
	 *
	 * @return	void
	 */
	public function run()
	{
		try
		{
			$method = $this->input->get('method', null, 'cmd');

			$callback = null;

			if (JFactory::getConfig()->get('jsonp_enabled'))
			{
				$callback = $this->input->get('callback', null, 'cmd');
			}

			$logger = AutotweetLogger::getInstance();
			$logger->log(JLog::INFO, 'Mapis run: method ' . $method);

			// Do not play around
			if (array_key_exists($method, $this->myMethods))
			{
				$localMethod = $this->myMethods[$method];
				$response = $this->$localMethod();
				echo TextUtil::encodeJsonSuccessPackage($response, $callback);
			}
			else
			{
				$result_message = JText::_('COM_AUTOTWEET_METHOD_NOT_FOUND');
				echo TextUtil::encodeJsonErrorPackage($result_message, $callback);
			}
		}
		catch (Exception $e)
		{
			$result_message = $e->getMessage();
			echo TextUtil::encodeJsonErrorPackage($result_message, $callback);
		}
	}

	/**
	 * getRequestToken.
	 *
	 * @return	object
	 */
	protected function getRequestToken()
	{
		$req = $this->getOAuthRequest();

		if (!$req)
		{
			return;
		}

		$logger = AutotweetLogger::getInstance();
		$logger->log(JLog::INFO, 'Mapis run: requestToken', $req);

		$token = JgOAuthServer::getInstance()->fetch_request_token($req);

		return $token;
	}

	/**
	 * getAccessToken.
	 *
	 * @return	object
	 */
	protected function getAccessToken()
	{
		$req = $this->getOAuthRequest();

		if (!$req)
		{
			return;
		}

		$logger = AutotweetLogger::getInstance();
		$logger->log(JLog::INFO, 'Mapis run: requestToken', $req);

		$token = JgOAuthServer::getInstance()->fetch_access_token($req);

		return $token;
	}

	/**
	 * getGlobals.
	 *
	 * @return	object
	 */
	protected function getGlobals()
	{
		$req = $this->getOAuthRequest();

		if (!$req)
		{
			return;
		}

		$jgOAuthServer = JgOAuthServer::getInstance();
		$jgOAuthServer->checkToken($req);

		$response = new StdClass;
		$response->flavour = VersionHelper::getFlavour();
		$response->list_limit = JFactory::getConfig()->get('list_limit');
		$response->offset = JFactory::getConfig()->get('offset');
		$response->channels = SelectControlHelper::appChannels();

		return $response;
	}

	/**
	 * execLogout.
	 *
	 * @return	bool
	 */
	protected function execLogout()
	{
		$req = $this->getOAuthRequest();

		if (!$req)
		{
			return;
		}

		$jgOAuthServer = JgOAuthServer::getInstance();
		$jgOAuthServer->checkToken($req);
		$jgOAuthServer->logout($req);
		JFactory::getApplication()->logout();

		return true;
	}

	/**
	 * checkToken.
	 *
	 * @return	bool
	 */
	protected function checkToken()
	{
		$req = $this->getOAuthRequest();

		if (!$req)
		{
			return;
		}

		$jgOAuthServer = JgOAuthServer::getInstance();
		$jgOAuthServer->checkToken($req);

		return true;
	}

	/**
	 * getRequestsChartData.
	 *
	 * @return	bool
	 */
	protected function getRequestsChartData()
	{
		$req = $this->getOAuthRequest();

		if (!$req)
		{
			return;
		}

		JgOAuthServer::getInstance()->checkToken($req);

		if (empty($this->data))
		{
			$this->data = new JRegistry;
			GridHelper::loadStats($this->data);
		}

		$requestsData = array(
			(object) array('label' => JText::_('COM_AUTOTWEET_TITLE_REQUESTS'),
				'value' => (int) $this->data->get('requests')),
			(object) array('label' => JText::_('COM_AUTOTWEET_TITLE_POSTS'),
				'value' => (int) $this->data->get('posts'))
		);

		$stats = new StdClass;
		$stats->data = $requestsData;

		return $stats;
	}

	/**
	 * getPostsChartData.
	 *
	 * @return	bool
	 */
	protected function getPostsChartData()
	{
		$req = $this->getOAuthRequest();

		if (!$req)
		{
			return;
		}

		JgOAuthServer::getInstance()->checkToken($req);

		if (empty($this->data))
		{
			$this->data = new JRegistry;
			GridHelper::loadStats($this->data);
		}

		$postsData  = array(
			(object) array('label' => SelectControlHelper::getTextForEnum('success'),
				'value' => (int) $this->data->get('p_success')),
			(object) array('label' => SelectControlHelper::getTextForEnum('cronjob'),
				'value' => (int) $this->data->get('cronjob')),
			(object) array('label' => SelectControlHelper::getTextForEnum('approve'),
				'value' => (int) $this->data->get('p_approve')),
			(object) array('label' => SelectControlHelper::getTextForEnum('cancelled'),
				'value' => (int) $this->data->get('p_cancelled')),
			(object) array('label' => SelectControlHelper::getTextForEnum('error'),
				'value' => (int) $this->data->get('p_error'))
		);

		$stats = new StdClass;
		$stats->data = $postsData;

		return $stats;
	}

	/**
	 * getPostsTimeline.
	 *
	 * @return	bool
	 */
	protected function getPostsTimeline()
	{
		$req = $this->getOAuthRequest();

		if (!$req)
		{
			return;
		}

		JgOAuthServer::getInstance()->checkToken($req);

		$data = new JRegistry;
		GridHelper::loadStatsTimeline($data);

		$stats = new StdClass;
		$stats->data = $data->get('timeline');

		return $stats;
	}

	/**
	 * getMenuitems.
	 *
	 * @return	string
	 */
	protected function getMenuitems()
	{
		$req = $this->getOAuthRequest();

		if (!$req)
		{
			return;
		}

		JgOAuthServer::getInstance()->checkToken($req);

		JFactory::getLanguage()->load('joomla', JPATH_ADMINISTRATOR);

		return EHtmlSelect::menuitemlist(
				null,
				'selectedMenuItem'
		);

		/*
,
				array(
					'ng-model' => "urlFieldCtrl.menuitemValue",
					'ng-change' => "urlFieldCtrl.loadUrl(urlFieldCtrl.menuitemValue)",
					'size' => 1
		 */
	}

	/**
	 * getUrlByItemId.
	 *
	 * @return	string
	 */
	protected function getUrlByItemId()
	{
		$req = $this->getOAuthRequest();

		if (!$req)
		{
			return;
		}

		JgOAuthServer::getInstance()->checkToken($req);

		$itemId = $this->input->get('itemId', 0, 'int');
		$url = 'index.php?Itemid=' . $itemId;
		$url = RouteHelp::getInstance()->getAbsoluteUrl($url);

		return $url;
	}

	/**
	 * applyAjaxPluginAction
	 *
	 * @return	bool
	 */
	protected function applyPlugin()
	{
		$req = $this->getOAuthRequest();

		if (!$req)
		{
			return;
		}

		JgOAuthServer::getInstance()->checkToken($req);

		$request = $this->input->get('request', 0, 'string');
		$input = new JRegistry;
		$input->loadString($request);
		$data = RequestHelp::getAjaxData($input);

		$controller = F0FController::getTmpInstance('com_autotweet', 'requests');
		$status = $controller->callPluginAction($data);

		if (!$status)
		{
			$errors = JFactory::getSession()->get('last_req_errors');
			throw new Exception(
				JText::sprintf('COM_AUTOTWEET_UNABLETO', 'applyPlugin - ' . $errors)
			);
		}

		$id = JFactory::getSession()->get('last_req_id');

		$message = array(
			'request_id' => $id,
			'message' => JText::_('COM_AUTOTWEET_COMPOSER_MESSAGE_SAVED'),
		);

		return $message;
	}

	/**
	 * applyAjaxOwnAction
	 *
	 * @return	bool
	 */
	protected function applyComposer()
	{
		$req = $this->getOAuthRequest();

		if (!$req)
		{
			return;
		}

		JgOAuthServer::getInstance()->checkToken($req);

		$request = $this->input->get('request', 0, 'string');
		$input = new JRegistry;
		$input->loadString($request);
		$data = RequestHelp::getAjaxData($input);

		$controller = F0FController::getTmpInstance('com_autotweet', 'requests');
		$status = $controller->callAjaxOwnAction($data);

		if (!$status)
		{
			$errors = JFactory::getSession()->get('last_req_errors');
			throw new Exception(
				JText::sprintf('COM_AUTOTWEET_UNABLETO', 'applyComposer - ' . $errors)
			);
		}

		$id = JFactory::getSession()->get('last_req_id');

		$message = array(
			'request_id' => $id,
			'message' => JText::_('COM_AUTOTWEET_COMPOSER_MESSAGE_SAVED'),
		);

		return $message;
	}

	/**
	 * browseRequests
	 *
	 * @return	$data
	 */
	protected function browseRequests()
	{
		$req = $this->getOAuthRequest();

		if (!$req)
		{
			return;
		}

		JgOAuthServer::getInstance()->checkToken($req);

		$params = $this->input->get('params', 0, 'string');
		$input = new JRegistry;
		$input->loadString($params);

		$config = array(
			'option' => 'com_autotweet',
			'view' => 'requests',
			'format' => 'json',
			'input' => $input->toArray()
		);

		$controller = F0FController::getTmpInstance('com_autotweet', 'requests', $config);

		ob_start();
		$status = $controller->browse();
		$items = ob_get_contents();
		ob_end_clean();

		if ($status)
		{
			$items = TextUtil::decodeJsonPackage($items);

			$collection = new StdClass;
			$collection->data = $items;

			return $collection;
		}

		return false;
	}

	/**
	 * readRequest
	 *
	 * @return	$data
	 */
	protected function readRequest()
	{
		$req = $this->getOAuthRequest();

		if (!$req)
		{
			return;
		}

		JgOAuthServer::getInstance()->checkToken($req);

		$params = $this->input->get('params', 0, 'string');
		$input = new JRegistry;
		$input->loadString($params);

		$config = array(
			'option' => 'com_autotweet',
			'view' => 'requests',
			'format' => 'json',
			'input' => $input->toArray()
		);

		$controller = F0FController::getTmpInstance('com_autotweet', 'requests', $config);

		ob_start();
		$status = $controller->read();
		$item = ob_get_contents();
		ob_end_clean();

		if ($status)
		{
			$item = TextUtil::decodeJsonPackage($item);

			$collection = new StdClass;
			$collection->data = $item;

			return $collection;
		}

		throw new Exception('Request not found.');
	}

	/**
	 * publishRequest
	 *
	 * @return	bool
	 */
	protected function publishRequest()
	{
		$req = $this->getOAuthRequest();

		if (!$req)
		{
			return;
		}

		JgOAuthServer::getInstance()->checkToken($req);

		$request = $this->input->get('request', 0, 'string');
		$input = new JRegistry;
		$input->loadString($request);

		$config = array(
				'option' => 'com_autotweet',
				'view' => 'requests',
				'format' => 'json',
				'input' => $input->toArray()
		);

		$controller = F0FController::getTmpInstance('com_autotweet', 'requests', $config);
		$status = $controller->callPublishAjaxAction();

		return $status;
	}

	/**
	 * cancelRequest
	 *
	 * @return	bool
	 */
	protected function cancelRequest()
	{
		$req = $this->getOAuthRequest();

		if (!$req)
		{
			return;
		}

		JgOAuthServer::getInstance()->checkToken($req);

		$request = $this->input->get('request', 0, 'string');
		$input = new JRegistry;
		$input->loadString($request);

		$config = array(
				'option' => 'com_autotweet',
				'view' => 'requests',
				'format' => 'json',
				'input' => $input->toArray()
		);

		$controller = F0FController::getTmpInstance('com_autotweet', 'requests', $config);
		$published = 1;
		$status = $controller->callMoveToState($published);

		return $status;
	}

	/**
	 * browsePosts
	 *
	 * @return	$data
	 */
	protected function browsePosts()
	{
		$req = $this->getOAuthRequest();

		if (!$req)
		{
			return;
		}

		JgOAuthServer::getInstance()->checkToken($req);

		$params = $this->input->get('params', 0, 'string');
		$input = new JRegistry;
		$input->loadString($params);

		$config = array(
				'option' => 'com_autotweet',
				'view' => 'posts',
				'format' => 'json',
				'input' => $input->toArray()
		);

		$controller = F0FController::getTmpInstance('com_autotweet', 'posts', $config);

		ob_start();
		$status = $controller->browse();
		$items = ob_get_contents();
		ob_end_clean();

		if ($status)
		{
			$items = TextUtil::decodeJsonPackage($items);

			$collection = new StdClass;
			$collection->data = $items;

			return $collection;
		}

		return false;
	}

	/**
	 * readPost
	 *
	 * @return	$data
	 */
	protected function readPost()
	{
		$req = $this->getOAuthRequest();

		if (!$req)
		{
			return;
		}

		JgOAuthServer::getInstance()->checkToken($req);

		$params = $this->input->get('params', 0, 'string');
		$input = new JRegistry;
		$input->loadString($params);

		$config = array(
				'option' => 'com_autotweet',
				'view' => 'posts',
				'format' => 'json',
				'input' => $input->toArray()
		);

		$controller = F0FController::getTmpInstance('com_autotweet', 'posts', $config);

		ob_start();
		$status = $controller->read();
		$item = ob_get_contents();
		ob_end_clean();

		if ($status)
		{
			$item = TextUtil::decodeJsonPackage($item);

			$collection = new StdClass;
			$collection->data = $item;

			return $collection;
		}

		throw new Exception('Post not found.');
	}

	/**
	 * publishPost
	 *
	 * @return	bool
	 */
	protected function publishPost()
	{
		$req = $this->getOAuthRequest();

		if (!$req)
		{
			return;
		}

		JgOAuthServer::getInstance()->checkToken($req);

		$post = $this->input->get('post', 0, 'string');
		$input = new JRegistry;
		$input->loadString($post);

		$config = array(
				'option' => 'com_autotweet',
				'view' => 'posts',
				'format' => 'json',
				'input' => $input->toArray()
		);

		$controller = F0FController::getTmpInstance('com_autotweet', 'posts', $config);
		$status = $controller->publishAjaxAction();

		return $status;
	}

	/**
	 * cancelPost
	 *
	 * @return	bool
	 */
	protected function cancelPost()
	{
		$req = $this->getOAuthRequest();

		if (!$req)
		{
			return;
		}

		JgOAuthServer::getInstance()->checkToken($req);

		$post = $this->input->get('post', 0, 'string');
		$input = new JRegistry;
		$input->loadString($post);

		$config = array(
				'option' => 'com_autotweet',
				'view' => 'posts',
				'format' => 'json',
				'input' => $input->toArray()
		);

		$controller = F0FController::getTmpInstance('com_autotweet', 'posts', $config);
		$status = $controller->cancelAjaxAction();

		return $status;
	}

	/**
	 * getOAuthRequest
	 *
	 * @return	object
	 */
	private function getOAuthRequest()
	{
		$data = $this->input->get('signature', null, 'STRING');

		if (empty($data))
		{
			// No JInputJSON in J2.5
			$raw = file_get_contents('php://input');
			$data = TextUtil::json_decode($raw, true);

			if (isset($data['signature']))
			{
				$data = $data['signature'];
			}
			else
			{
				$data = null;
			}
		}
		else
		{
			$data = TextUtil::json_decode($data, true);
		}

		if (empty($data))
		{
			throw new Exception('OAuth Communication Failure');
		}

		$signature_base_string = $data['signature_base_string'];
		$authorization_header = $data['authorization_header'];
		$signature = $data['signature'];

		$safeHtmlFilter = JFilterInput::getInstance();
		$signature_base_string = $safeHtmlFilter->clean($signature_base_string, 'STRING');
		$authorization_header = $safeHtmlFilter->clean($authorization_header, 'STRING');
		$signature = $safeHtmlFilter->clean($signature, 'STRING');

		$parameters = XTOAuth\OAuthUtil::parse_parameters($signature_base_string);
		list($http_method, $http_url, $parameters) = array_keys($parameters);
		$parameters = XTOAuth\OAuthUtil::parse_parameters($parameters);
		$parameters['oauth_signature'] = XTOAuth\OAuthUtil::urldecode_rfc3986($signature);
		$req = new XTOAuth\OAuthRequest($http_method, $http_url, $parameters);
		$req->base_string = $signature_base_string;

		return $req;
	}

	/**
	 * Creates a new model object
	 *
	 * @param   string  $name    The name of the model class, e.g. Items
	 * @param   string  $prefix  The prefix of the model class, e.g. FoobarModel
	 * @param   array   $config  The configuration parameters for the model class
	 *
	 * @return  F0FModel  The model object
	 */
	protected function createModel($name, $prefix = '', $config = array())
	{
		$modelName = 'Requests';
		$classPrefix = 'AutoTweetModel';

		$result = F0FModel::getAnInstance($modelName, $classPrefix, $config);

		return $result;
	}

	/**
	 * uploadFile
	 *
	 * @return	bool
	 */
	protected function uploadFile()
	{
		$req = $this->getOAuthRequest();

		if (!$req)
		{
			return;
		}

		JgOAuthServer::getInstance()->checkToken($req);

		$imageurl = null;

		$receivedFile = $this->input->files->get('file');
		$file = $receivedFile['tmp_name'];

		if ( ($file) && (file_exists($file)) )
		{
			$filename = $receivedFile['name'];
			$image = JPATH_JOOCIAL_APP_MEDIA . '/' . $filename;

			if (JFile::upload($file, $image))
			{
				$imageurl = str_replace(JPATH_ROOT, '', $image);
				$imageurl = RouteHelp::getInstance()->getAbsoluteUrl($imageurl, true);
			}
		}

		return $imageurl;
	}
}
