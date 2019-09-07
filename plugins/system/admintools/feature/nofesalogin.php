<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

class AtsystemFeatureNofesalogin extends AtsystemFeatureAbstract
{
	protected $loadOrder = 900;

	/**
	 * Is this feature enabled?
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		if (!$this->container->platform->isFrontend())
		{
			return false;
		}

		if ($this->cparams->getValue('nofesalogin', 0) != 1)
		{
			return false;
		}

		return true;
	}

	public function onUserLogin($user, $options)
	{
		$instance = $this->getUserObject($user, $options);

		$isSuperAdmin = $instance->authorise('core.admin');

		if (!$isSuperAdmin)
		{
			return true;
		}

		// Is this a Joomla! 3.9+ installation with a user who's not yet provided consent?
		if ($this->isJoomlaPrivacyEnabled())
		{
			$userID     = JUserHelper::getUserId($user['username']);
			$userObject = JFactory::getUser($userID);

			if (!$this->hasUserConsented($userObject))
			{
				return true;
			}
		}

		$newopts = array();
		$this->app->logout($instance->id, $newopts);

		// Since Joomla! 2.5.5 you have to close the session before throwing an error, otherwise the user isn't
		// logged out.
		$session = JFactory::getSession();
		$session->close();

		// Throw error
		throw new Exception(JText::_('JGLOBAL_AUTH_ACCESS_DENIED'), 403);
	}

	function &getUserObject($user, $options = array())
	{
		JLoader::import('joomla.user.helper');
		$instance = new JUser();

		if ($id = intval(JUserHelper::getUserId($user['username'])))
		{
			$instance->load($id);

			return $instance;
		}

		JLoader::import('joomla.application.component.helper');
		$config = JComponentHelper::getParams('com_users');
		$defaultUserGroup = $config->get('new_usertype', 2);

		$instance->set('id', 0);
		$instance->set('name', $user['fullname']);
		$instance->set('username', $user['username']);
		$instance->set('email', $user['email']); // Result should contain an email (check)
		$instance->set('usertype', 'deprecated');
		$instance->set('groups', array($defaultUserGroup));

		return $instance;
	}
}
