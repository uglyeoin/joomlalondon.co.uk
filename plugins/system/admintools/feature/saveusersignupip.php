<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use FOF30\Date\Date;

defined('_JEXEC') or die;

class AtsystemFeatureSaveusersignupip extends AtsystemFeatureAbstract
{
	protected $loadOrder = 910;

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

		if ($this->cparams->getValue('saveusersignupip', 0) != 1)
		{
			return false;
		}

		return true;
	}

	public function onUserAfterSave($user, $isnew, $success, $msg)
	{
		$process = true;

		// Only trigger on successful user creation
		if (!$success)
		{
			$process = false;
		}

		// Only trigger on new user creation, not subsequent edits
		if (!$isnew)
		{
			$process = false;
		}

		// Only trigger on front-end user creation.
		if (!$this->container->platform->isFrontend())
		{
			$process = false;
		}

		if (!$process)
		{
			return;
		}

		// Create a new user note

		// Get the user's ID
		$user_id = (int)$user['id'];

		// Get the IP address
		$ip = AtsystemUtilFilter::getIp();

		if ((strpos($ip, '::') === 0) && (strstr($ip, '.') !== false))
		{
			$ip = substr($ip, strrpos($ip, ':') + 1);
		}

		// Get the user agent string
		$user_agent = $_SERVER['HTTP_USER_AGENT'];

		// Get current date and time in database format
		JLoader::import('joomla.utilities.date');
		$now = new Date();
		$now = $now->toSql();

		// Load the component's administrator translation files
		$jlang = JFactory::getLanguage();
		$jlang->load('com_admintools', JPATH_ADMINISTRATOR, 'en-GB', true);
		$jlang->load('com_admintools', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
		$jlang->load('com_admintools', JPATH_ADMINISTRATOR, null, true);

		// Create and save the user note
		$userNote = (object)array(
			'user_id'         => $user_id,
			'catid'           => 0,
			'subject'         => JText::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_SIGNUPIP_SUBJECT'),
			'body'            => JText::sprintf('COM_ADMINTOOLS_LBL_CONFIGUREWAF_SIGNUPIP_BODY', $ip, $user_agent),
			'state'           => 1,
			'created_user_id' => 42,
			'created_time'    => $now
		);

		try
		{
			$this->db->insertObject('#__user_notes', $userNote, 'id');
		}
		catch (Exception $e)
		{
			// Do nothing if the save fails
		}
	}
}
