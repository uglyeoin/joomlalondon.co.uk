<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use FOF30\Date\Date;

defined('_JEXEC') or die;

class AtsystemFeatureTrackfailedlogins extends AtsystemFeatureAbstract
{
	protected $loadOrder = 800;

	/**
	 * Is this feature enabled?
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		return ($this->cparams->getValue('trackfailedlogins', 0) == 1);
	}

	/**
	 * Treat failed logins as security exceptions
	 *
	 * @param JAuthenticationResponse $response
	 */
	public function onUserLoginFailure($response)
	{
		// Exit if the IP is blacklisted; logins originating from blacklisted IPs will be blocked anyway
		if ($this->parentPlugin->runBooleanFeature('isIPBlocked', false, []))
		{
			return;
		}

		$user = $this->input->getString('username', null);
		$pass = $this->input->getString('password', null);

		if (empty($pass))
		{
			$pass = $this->input->getString('passwd', null);
		}

		$extraInfo = null;

		if (!empty($user))
		{
			$extraInfo = 'Username: ' . $user;
		}

		$this->exceptionsHandler->logAndAutoban('loginfailure', $user, $extraInfo);

		$this->deactivateUser($user);
	}

	private function deactivateUser($username)
	{
		$userParams = JComponentHelper::getParams('com_users');

		// User registration disabled or no user activation - Let's stop here
		if (!$userParams->get('allowUserRegistration') || ($userParams->get('useractivation') == 0))
		{
			return;
		}

		$ip = AtsystemUtilFilter::getIp();

		// If I can't detect the IP there's not point in continuing
		if (!$ip)
		{
			return;
		}

		$limit     = $this->cparams->getValue('deactivateusers_num', 3);
		$numfreq   = $this->cparams->getValue('deactivateusers_numfreq', 1);
		$frequency = $this->cparams->getValue('deactivateusers_frequency', 'hour');

		// The user didn't set any limit nor frequency value, let's stop here
		if (!$limit || !$numfreq)
		{
			return;
		}

		$userid = JUserHelper::getUserId($username);

		// The user doesn't exists, let's stop here
		if (!$userid)
		{
			return;
		}

		$user = $this->container->platform->getUser($userid);

		// Username doesn't match, the user is blocked or is not active? Let's stop here
		if ($user->username != $username || $user->block || !(empty($user->activation)))
		{
			return;
		}

		// If I'm here, it means that this is a valid user, let's see if I have to deactivate him
		$where = array(
			'ip'     => $ip,
			'reason' => 'loginfailure',
		);

		$deactivate = $this->checkLogFrequency($limit, $numfreq, $frequency, $where);

		if (!$deactivate)
		{
			return;
		}

		JPluginHelper::importPlugin('user');
		$db = $this->db;

		$randomPassword        = class_exists('Joomla\\CMS\\User\\UserHelper') ? \Joomla\CMS\User\UserHelper::genRandomPassword() : \JUserHelper::genRandomPassword();
		$data['activation']    = class_exists('Joomla\\CMS\\Application\\ApplicationHelper') ? \Joomla\CMS\Application\ApplicationHelper::getHash($randomPassword) : JApplication::getHash($randomPassword);
		$data['block']         = 1;
		$data['lastvisitDate'] = $db->getNullDate();

		// If an admin needs to activate the user, I have to set the activate flag
		if ($userParams->get('useractivation') == 2)
		{
			$user->setParam('activate', 1);
		}

		if (!$user->bind($data))
		{
			return;
		}

		if (!$user->save())
		{
			return;
		}

		// Ok, now it's time to send the activation email again
		$template = $this->exceptionsHandler->getEmailTemplate('user-reactivate', true);

		// Well, this should never happen...
		if (!$template)
		{
			return;
		}

		$subject = $template[0];
		$body    = $template[1];

		$config = $this->container->platform->getConfig();

		try
		{
			$mailer = JFactory::getMailer();

			$mailfrom = $config->get('mailfrom');
			$fromname = $config->get('fromname');

			$uri      = JUri::getInstance();
			$base     = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
			$activate = $base . JRoute::_('index.php?option=com_users&task=registration.activate&token=' . $data['activation'], false);

			// Send e-mail to the user
			if ($userParams->get('useractivation') == 1)
			{
				$mailer->addRecipient($user->email);
			}
			// Send e-mail to Super Users
			elseif ($userParams->get('useractivation') == 2)
			{
				// get all admin users
				$query = $db->getQuery(true)
					->select($db->qn(array('name', 'email', 'sendEmail', 'id')))
					->from($db->qn('#__users'))
					->where($db->qn('sendEmail') . ' = ' . 1);

				$rows = $db->setQuery($query)->loadObjectList();

				// Send mail to all users with users creating permissions and receiving system emails
				foreach ($rows as $row)
				{
					$usercreator = $this->container->platform->getUser($row->id);

					if ($usercreator->authorise('core.create', 'com_users') && !empty($usercreator->email))
					{
						$mailer->addRecipient($usercreator->email);
					}
				}
			}
			else
			{
				// Future-proof check
				return;
			}

			$tokens = $this->exceptionsHandler->getEmailVariables('', [
				'[ACTIVATE]' => '<a href="' . $activate . '">' . $activate . '</a>',
				'[USER]'     => $user->username . ' (' . $user->name . ' <' . $user->email . '>)',
			]);

			$subject = str_replace(array_keys($tokens), array_values($tokens), $subject);
			$body    = str_replace(array_keys($tokens), array_values($tokens), $body);

			// This line is required because SpamAssassin is BROKEN
			$mailer->Priority = 3;

			$mailer->isHtml(true);
			$mailer->setSender(array($mailfrom, $fromname));
			$mailer->setSubject($subject);
			$mailer->setBody($body);
			$mailer->Send();
		}
		catch (\Exception $e)
		{
			// Joomla! 3.5 and later throw an exception when crap happens instead of suppressing it and returning false
		}
	}

	/**
	 * @param       $limit
	 * @param       $numfreq
	 * @param       $frequency
	 * @param array $extraWhere
	 *
	 * @return bool
	 */
	private function checkLogFrequency($limit, $numfreq, $frequency, array $extraWhere)
	{
		JLoader::import('joomla.utilities.date');
		$db = $this->db;

		$mindatestamp = 0;

		switch ($frequency)
		{
			case 'second':
				break;

			case 'minute':
				$numfreq *= 60;
				break;

			case 'hour':
				$numfreq *= 3600;
				break;

			case 'day':
				$numfreq *= 86400;
				break;

			case 'ever':
				$mindatestamp = 946706400; // January 1st, 2000
				break;
		}

		$jNow = new Date();

		if ($mindatestamp == 0)
		{
			$mindatestamp = $jNow->toUnix() - $numfreq;
		}

		$jMinDate = new Date($mindatestamp);
		$minDate = $jMinDate->toSql();

		$sql = $db->getQuery(true)
			->select('COUNT(*)')
			->from($db->qn('#__admintools_log'))
			->where($db->qn('logdate') . ' >= ' . $db->q($minDate));

		foreach ($extraWhere as $column => $value)
		{
			$sql->where($db->qn($column) . ' = ' . $db->q($value));
		}

		$db->setQuery($sql);

		try
		{
			$numOffenses = $db->loadResult();
		}
		catch (Exception $e)
		{
			$numOffenses = 0;
		}

		if ($numOffenses < $limit)
		{
			return false;
		}

		return true;
	}
}
