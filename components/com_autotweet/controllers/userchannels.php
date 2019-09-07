<?php

/**
 * @package     Extly.Components
 * @subpackage  com_xtdir - Extended Directory for SobiPro
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

defined('_JEXEC') or die;

/**
 * AutotweetControllerUserChannels
 *
 * @package     Extly.Components
 * @subpackage  com_autotweet
 * @since       1.0
 */
class AutotweetControllerUserChannels extends ExtlyController
{
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
		return parent::display(false, $urlparams);
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
		$result = parent::createModel($name, $prefix, $config);
		$result->setState('scope', 'U');
		$result->setState('published', 'nofilter');

		return $result;
	}

	/**
	 * getAuthParams.
	 *
	 * @return	void
	 */
	public function getAuthParams()
	{
		@ob_end_clean();
		header('Content-type: text/plain');

		// No JInputJSON in J2.5
		$raw = file_get_contents('php://input');
		$data = TextUtil::json_decode($raw, true);

		$safeHtmlFilter = JFilterInput::getInstance();

		$token = $data['token'];
		$token = $safeHtmlFilter->clean($token, 'ALNUM');
		$this->input->set($token, 1);

		// CSRF prevention
		if ($this->csrfProtection)
		{
			$this->_csrfProtection();
		}

		try
		{
			$channelTypeId = null;
			$channelId = null;

			if (array_key_exists('channelTypeId', $data))
			{
				$channelTypeId = $data['channelTypeId'];
				$channelTypeId = $safeHtmlFilter->clean($channelTypeId, 'INT');
			}

			if (array_key_exists('channelId', $data))
			{
				$channelId = $data['channelId'];
				$channelId = $safeHtmlFilter->clean($channelId, 'INT');
			}

			$model = $this->getThisModel();

			if ($channelTypeId)
			{
				// Parent Channel Check
				$parentChannel = $model->loadParentChannel($channelTypeId);
			}
			else
			{
				if (!$channelId)
				{
					throw new Exception('Channel is empty');
				}

				$channel = $model->getitem($channelId);
				$channelTypeId = $channel->channeltype_id;
			}

			$channelType = F0FModel::getTmpInstance('ChannelTypes', 'AutoTweetModel')->getParamsForm($channelTypeId);

			$callback = 'onAuth' . ucwords($channelType);
			$params = $model->getUserAuthParams($channelTypeId, $channelId);

			$message = array(
				'callback' => $callback,
				'params' => $params
			);
			echo TextUtil::encodeJsonSuccessPackage($message);
		}
		catch (Exception $e)
		{
			$result_message = $e->getMessage();
			echo TextUtil::encodeJsonErrorPackage($result_message);
		}

		flush();
		JFactory::getApplication()->close();
	}

	/**
	 * addAuthChannel.
	 *
	 * @return	void
	 */
	public function addAuthChannel()
	{
		@ob_end_clean();
		header('Content-type: text/plain');

		// No JInputJSON in J2.5
		$raw = file_get_contents('php://input');
		$data = TextUtil::json_decode($raw, true);

		$safeHtmlFilter = JFilterInput::getInstance();

		$token = $data['token'];
		$token = $safeHtmlFilter->clean($token, 'ALNUM');
		$this->input->set($token, 1);

		// CSRF prevention
		if ($this->csrfProtection)
		{
			$this->_csrfProtection();
		}

		try
		{
			if (!array_key_exists('authParams', $data))
			{
				throw new Exception('Wrong authParams (addAuthChannel)!');
			}

			$data = $data['authParams'];

			$channelTypeId = $data['channeltype_id'];
			$channelTypeId = $safeHtmlFilter->clean($channelTypeId, 'INT');

			// Parent Channel Check
			$model = $this->getThisModel();
			$parentChannel = $model->loadParentChannel($channelTypeId);

			$channel = $model->getDataFromParent($parentChannel);
			$status = $this->_applySave($channelTypeId, $channel, $data);

			if ($status)
			{
				$id = $model->getId();
				$session = JFactory::getSession();
				$session->set($model->getHash() . 'savedata', null);

				$message = $this->_getChannelStatusMessage($id);
				echo TextUtil::encodeJsonSuccessPackage($message);
			}
			else
			{
				$result_message = $model->getErrors();
				echo TextUtil::encodeJsonErrorPackage($result_message);
			}
		}
		catch (Exception $e)
		{
			$result_message = $e->getMessage();
			echo TextUtil::encodeJsonErrorPackage($result_message);
		}

		flush();
		JFactory::getApplication()->close();
	}

	/**
	 * publishAction.
	 *
	 * @return	void
	 */
	public function publishAction()
	{
		@ob_end_clean();
		header('Content-type: text/plain');

		// CSRF prevention
		if ($this->csrfProtection)
		{
			$this->_csrfProtection();
		}

		try
		{
			$input = new F0FInput;
			$channelId = $input->get('channelId', 0, 'INT');

			$authParams = $input->get('authParams', 0, 'ARRAY');

			if (!$authParams)
			{
				throw new Exception('Wrong authParams (publishAction)!');
			}

			$data = $authParams;
			$channelTypeId = $authParams['channeltype_id'];

			$model = $this->getThisModel();
			$this->_checkAclPerms($channelId);

			$model->setId($channelId);
			$channel = $model->getItem();
			$channel = $model->getDataFromChannel($channel);
			$status = $this->_applySave($channelTypeId, $channel, $data);

			if ($status)
			{
				$status = parent::publish();
			}

			if ($status)
			{
				$message = $this->_getChannelStatusMessage($channelId);
				echo TextUtil::encodeJsonSuccessPackage($message);
			}
			else
			{
				$result_message = JText::sprintf('COM_AUTOTWEET_UNABLETO', 'publishAction');
				echo TextUtil::encodeJsonErrorPackage($result_message);
			}
		}
		catch (Exception $e)
		{
			$result_message = $e->getMessage();
			echo TextUtil::encodeJsonErrorPackage($result_message);
		}

		flush();
		JFactory::getApplication()->close();
	}

	/**
	 * unpublishAction.
	 *
	 * @return	void
	 */
	public function unpublishAction()
	{
		@ob_end_clean();
		header('Content-type: text/plain');

		// CSRF prevention
		if ($this->csrfProtection)
		{
			$this->_csrfProtection();
		}

		try
		{
			$input = new F0FInput;
			$channelId = $input->get('channelId', 0, 'INT');

			$model = $this->getThisModel();
			$this->_checkAclPerms($channelId);

			$model->setId($channelId);
			parent::unpublish();

			$message = $this->_getChannelStatusMessage($channelId);
			echo TextUtil::encodeJsonSuccessPackage($message);
		}
		catch (Exception $e)
		{
			$result_message = $e->getMessage();
			echo TextUtil::encodeJsonErrorPackage($result_message);
		}

		flush();
		JFactory::getApplication()->close();
	}

	/**
	 * _getChannelStatusMessage
	 *
	 * @param   int  $channelId  Params
	 *
	 * @return array
	 */
	private function _getChannelStatusMessage($channelId)
	{
		$model = $this->getThisModel();
		$channel = $model->getItem($channelId);

		return array(
						'id' => $channel->id,
						'name' => $channel->name,
						'typeName' => SelectControlHelper::getChanneltypeName($channel->channeltype_id),
						'authorized' => true,
						'published' => ($channel->published ? true : false),

						'lbl_published' => JText::_('COM_AUTOTWEET_USERCHANNELS_PUBLISHED'),
						'lbl_publish_item' => JText::_('COM_AUTOTWEET_USERCHANNELS_PUBLISH_ITEM'),
						'lbl_unpublished' => JText::_('COM_AUTOTWEET_USERCHANNELS_UNPUBLISHED'),
						'lbl_unpublish_item' => JText::_('COM_AUTOTWEET_USERCHANNELS_UNPUBLISH_ITEM'),
						'lbl_authorized' => JText::_('COM_AUTOTWEET_USERCHANNELS_AUTHORIZED'),
						'lbl_authorize_item' => JText::_('COM_AUTOTWEET_USERCHANNELS_AUTHORIZE_ITEM'),
						'lbl_unauthorized' => JText::_('COM_AUTOTWEET_USERCHANNELS_UNAUTHORIZED')
		);
	}

	/**
	 * _checkAclPerms
	 *
	 * @param   int  $channelId  Params
	 *
	 * @return bool
	 */
	private function _checkAclPerms($channelId)
	{
		$model = $this->getThisModel();
		$model->setState('channel_id', $channelId);
		$channel = $model->getFirstItem();

		if ((!$channel) || ($channel->id != $channelId))
		{
			throw new Exception('Ownership failed!');
		}
	}

	/**
	 * _applySave
	 *
	 * @param   int    $channelTypeId  Params
	 * @param   array  &$channel       Params
	 * @param   array  &$data          Params
	 *
	 * @return bool
	 */
	private function _applySave($channelTypeId, &$channel, &$data)
	{
		$model = $this->getThisModel();
		$model->getDataFromAuth($channelTypeId, $channel, $data);

		if (!$model->getId())
		{
			$model->setIDsFromData($data);
		}

		$id = $model->getId();

		if (!$this->onBeforeApplySave($channel))
		{
			return false;
		}

		// Set the layout to form, if it's not set in the URL

		if (is_null($this->layout))
		{
			$this->layout = 'form';
		}

		// Do I have a form?
		$model->setState('form_name', 'form.' . $this->layout);

		$status = $model->save($channel);

		if ($status && ($id != 0))
		{
			// Try to check-in the record if it's not a new one
			$status = $model->checkin();

			if ($status)
			{
				$status = $this->onAfterApplySave();
			}
		}

		return $status;
	}

	/**
	 * twCallback
	 *
	 * @return array
	 */
	public function twCallback()
	{
		// CSRF prevention
		// No check, it depends on session and Twitter params

		$session = JFactory::getSession();
		$channelTypeId = $session->get('channelTypeId');
		$channelId = $session->get('channelId');

		// Parent Channel Check
		$model = $this->getThisModel();

		if ($channelId)
		{
			$model->setId($channelId);
		}

		$parentChannel = $model->loadParentChannel($channelTypeId);
		$xtform = EForm::paramsToRegistry($parentChannel);

		$consumer_key = $xtform->get('consumer_key');
		$consumer_secret = $xtform->get('consumer_secret');
		$appHelper = new TwAppHelper($consumer_key, $consumer_secret);

		// Access_token - access_token_secret
		$tokens = $appHelper->getAccessToken();

		if (!$tokens)
		{
			throw new Exception('Invalid tokens in Callback');
		}

		$access_token = $tokens['access_token'];
		$access_token_secret = $tokens['access_token_secret'];

		$appHelper = new TwAppHelper($consumer_key, $consumer_secret, $access_token, $access_token_secret);
		$appHelper->login();

		$result = $appHelper->verify();

		if (!$result['status'])
		{
			throw new Exception(
				JText::sprintf('COM_AUTOTWEET_UNABLETO', 'Twitter Callback Verification')
			);
		}

		$data = $tokens;
		$data['user_id'] = $result['user']->id;
		$data['social_url'] = $result['url'];

		// OK, We have a new channel!

		// Parent Channel Check
		$parentChannel = $model->loadParentChannel($channelTypeId);
		$channel = $model->getDataFromParent($parentChannel);

		if ($channelId)
		{
			$channel['id'] = $channelId;
		}

		$status = $this->_applySave($channelTypeId, $channel, $data);

		if (!$status)
		{
			throw new Exception(
				JText::sprintf('COM_AUTOTWEET_UNABLETO', 'Twitter Callback Save')
			);
		}

		// Redirect to the display task
		$this->setRedirect(JUri::current());
	}

	/**
	 * liOAuth2Callback
	 *
	 * @return array
	 */
	public function liOAuth2Callback()
	{
		// CSRF prevention
		// No check, it depends on session and Twitter params

		$session = JFactory::getSession();
		$channelTypeId = $session->get('channelTypeId');
		$channelId = $session->get('channelId');

		// Parent Channel Check
		$model = $this->getThisModel();

		if ($channelId)
		{
			$model->setId($channelId);
		}

		$parentChannel = $model->loadParentChannel($channelTypeId);

		$code = $this->input->getString('code');
		$state = $this->input->getString('state');

		if (!empty($code))
		{
			$lioauth2ChannelHelper = new LiOAuth2ChannelHelper($parentChannel);
			$access_token = $lioauth2ChannelHelper->getAccessToken($code, $state);

			if (!$access_token)
			{
				throw new Exception('Invalid tokens in Li OAuth2 Callback');
			}

			$result = $lioauth2ChannelHelper->getUser();

			if (!isset($result['status']))
			{
				throw new Exception(
					JText::sprintf('COM_AUTOTWEET_UNABLETO', 'LinkedIn OAuth2 Callback Verification')
				);
			}

			// OK, We have a new channel!

			$data['access_token'] = $access_token->getToken();

			$now = null;
			$expiresAt = $access_token->getExpiresAt();

			if ($expiresAt)
			{
				$expiresAt = $expiresAt->getTimestamp();
				$now = JFactory::getDate($expiresAt)->toSql();
			}
						;
			$data['expires_in'] = $now;
			$data['user_id'] = $result['user']->id;
			$data['social_url'] = $result['url'];

			$now = JFactory::getDate()->toUnix() + $access_token->expires_in;
			$now = JFactory::getDate($now)->toSql();
			$data['expires_date'] = $now;

			// Parent Channel Check
			$channel = $model->getDataFromParent($parentChannel);

			if ($channelId)
			{
				$channel['id'] = $channelId;
			}

			$status = $this->_applySave($channelTypeId, $channel, $data);

			if (!$status)
			{
				throw new Exception(
					JText::sprintf('COM_AUTOTWEET_UNABLETO', 'LinkedIn Callback Save')
				);
			}
			else
			{
				JFactory::getApplication()->enqueueMessage(
					JText::_('COM_AUTOTWEET_USERCHANNEL_AUTHORIZED'),
					'info'
				);
			}

			// Redirect to the display task
			$this->setRedirect(JUri::current());
		}
	}
}
