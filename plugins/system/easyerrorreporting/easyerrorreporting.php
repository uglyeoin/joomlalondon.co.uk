<?php
/**
 * @Copyright
 * @package     EER - Easy Error Reporting for Joomla! 3.x
 * @author      Viktor Vogel <admin@kubik-rubik.de>
 * @version     3.1.1 - 2016-02-08
 * @link        https://joomla-extensions.kubik-rubik.de/eer-easy-error-reporting
 *
 * @license     GNU/GPL
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
defined('_JEXEC') or die('Restricted access');

class PlgSystemEasyErrorReporting extends JPlugin
{
	/**
	 * PlgSystemEasyErrorReporting constructor.
	 *
	 * @param object $subject
	 * @param array  $config
	 */
	function __construct(&$subject, $config)
	{
		$app = JFactory::getApplication();

		if($app->isAdmin())
		{
			return;
		}

		parent::__construct($subject, $config);
	}

	/**
	 * Sets the error level in the system trigger onAfterInitialise
	 */
	public function onAfterInitialise()
	{
		$error_level = (int)$this->params->get('error_level', 0);
		$allowed_usergroup = $this->allowedUserGroups();

		if(!empty($error_level) AND $allowed_usergroup)
		{
			if($error_level == 1)
			{
				error_reporting(0);
			}
			elseif($error_level == 2)
			{
				error_reporting(E_ERROR | E_WARNING | E_PARSE);
				ini_set('display_errors', 1);
			}
			elseif($error_level == 3)
			{
				error_reporting(E_ALL);
				ini_set('display_errors', 1);
			}
			elseif($error_level == 4)
			{
				error_reporting(-1);
				ini_set('display_errors', 1);
			}
		}
	}

	/**
	 * Checks the user group of the current user and returns true if the user belongs to a selected group
	 *
	 * @return bool
	 */
	private function allowedUserGroups()
	{
		$filter_groups = array_map('intval', $this->params->get('filter_groups', array(8)));
		$user_groups = JAccess::getGroupsByUser(JFactory::getUser()->id);

		foreach($user_groups as $user_group)
		{
			foreach($filter_groups as $filter_groups_value)
			{
				if($user_group == $filter_groups_value)
				{
					return true;
				}
			}
		}

		return false;
	}
}
