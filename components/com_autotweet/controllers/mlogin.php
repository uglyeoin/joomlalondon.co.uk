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

/**
 * AutotweetControllerMlogin
 *
 * @package     Extly.Components
 * @subpackage  com_autotweet
 * @since       1.0
 */
class AutotweetControllerMlogin extends ExtlyController
{
	/**
	 * Public constructor of the Controller class
	 *
	 * @param   array  $config  Optional configuration parameters
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->layout = 'mlogin';
	}

	/**
	 * Default task. Assigns a model to the view and asks the view to render
	 * itself.
	 *
	 * YOU MUST NOT USETHIS TASK DIRECTLY IN A URL. It is supposed to be
	 * used ONLY inside your code. In the URL, use task=browse instead.
	 *
	 * @param   bool    $cachable   Is this view cacheable?
	 * @param   bool    $urlparams  Add your safe URL parameters (see further down in the code)
	 * @param   string  $tpl        The name of the template file to parse
	 *
	 * @return  bool
	 */
	public function display($cachable = false, $urlparams = false, $tpl = null)
	{
		return parent::display(false, $urlparams, $tpl);
	}

	/**
	 * Method to log in a user.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function login()
	{
		JSession::checkToken('post') or jexit(JText::_('JInvalid_Token'));

		$app    = JFactory::getApplication();
		$input  = $app->input;
		$method = $input->getMethod();

		// Populate the data array:
		$data = array();

		$oauth_token = $app->input->post->get('oauth_token', '', 'BASE64');
		$oauth_token = base64_decode($oauth_token);

		$logger = AutotweetLogger::getInstance();
		$logger->log(JLog::INFO, 'Mlogin login: oauth_token ' . $oauth_token);

		if (empty($oauth_token))
		{
			$logger->log(JLog::ERROR, 'Mlogin login: No OAuth token');

			throw new Exception('No OAuth token');
		}

		$data['username']  = $input->$method->get('username', '', 'USERNAME');
		$data['password']  = $input->$method->get('password', '', 'RAW');
		$data['secretkey'] = $input->$method->get('secretkey', '', 'RAW');

		$logger->log(JLog::INFO, 'Mlogin login: username ' . $data['username']);

		// Get the log in options.
		$options = array();
		$options['remember'] = $this->input->getBool('remember', false);

		// Get the log in credentials.
		$credentials = array();
		$credentials['username']  = $data['username'];
		$credentials['password']  = $data['password'];
		$credentials['secretkey'] = $data['secretkey'];

		// Perform the log in.
		if (true === $app->login($credentials, $options))
		{
			$logger->log(JLog::INFO, 'Mlogin login: username ' . $data['username'] . ' - OK');

			$user = JFactory::getUser();
			$perms_manage = $user->authorise('core.manage', 'com_autotweet');

			if ($perms_manage)
			{
				// Success
				if ($options['remember'] == true)
				{
					$app->setUserState('rememberLogin', true);
				}

				$logger->log(JLog::INFO, 'Mlogin login: username ' . $data['username'] . ' - success');

				$url = JgOAuthServer::getInstance()->getVerifierCallback($oauth_token);
				$logger->log(JLog::INFO, 'Mlogin login: redirecting ' . $url);

				$app->redirect(JRoute::_($url, false));
			}
			else
			{
				$logger->log(JLog::ERROR, 'Mlogin login: ' . $data['username'] . ' - Only administrators are allowed');

				// $app->enqueueMessage('Only administrators are allowed', 'error');
				// $app->redirect(JRoute::_('index.php', false));

				// Failed, returning but no oauth_verifier
				$url = JgOAuthServer::getInstance()->getErrorCallback($oauth_token, 'Only administrators are allowed.');
				$logger->log(JLog::INFO, 'Mlogin login: redirecting ' . $url);

				$app->redirect(JRoute::_($url, false));
			}
		}
		else
		{
			// Login failed !
			$data['remember'] = (int) $options['remember'];
			$app->setUserState('users.login.form.data', $data);

			// Failed, returning but no oauth_verifier
			$url = JgOAuthServer::getInstance()->getErrorCallback($oauth_token, 'Username and password do not match or you do not have an account yet.');
			$logger->log(JLog::INFO, 'Mlogin login: redirecting ' . $url);

			$app->redirect(JRoute::_($url, false));
		}
	}

	/**
	 * Method to log out a user.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function logout()
	{
		JSession::checkToken('request') or jexit(JText::_('JInvalid_Token'));

		$app = JFactory::getApplication();

		// Perform the log in.
		$error  = $app->logout();
		$input  = $app->input;
		$method = $input->getMethod();

		// Check if the log out succeeded.
		if (!($error instanceof Exception))
		{
			// Get the return url from the request and validate that it is internal.
			$return = $input->$method->get('return', '', 'BASE64');
			$return = base64_decode($return);

			if ((!JUri::isInternal($return)) || empty($return))
			{
				$return = 'index.php?option=com_autotweet&view=mlogin';
			}

			// Redirect the user.
			$app->redirect(JRoute::_($return, false));
		}
		else
		{
			$app->redirect(JRoute::_('index.php?option=com_autotweet&view=mlogin', false));
		}
	}
}
