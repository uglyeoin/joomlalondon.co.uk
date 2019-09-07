<?php

/**
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

defined('_JEXEC') or die;

/**
 * AutoTweetDispatcher
 *
 * @package     Extly.Components
 * @subpackage  com_autotweet
 * @since       1.0
 */
class AutotweetDispatcher extends F0FDispatcher
{
	/**
	 * onBeforeDispatch.
	 *
	 * @return	bool
	 */
	public function onBeforeDispatch()
	{
		$result = parent::onBeforeDispatch();

		if (($result) && (!F0FPlatform::getInstance()->isCli()))
		{
			$view = $this->input->getCmd('view');
			Extly::loadStyle(true, ($view != 'composer'));

			$document = JFactory::getDocument();
			$document->addStyleSheet(JUri::root() . 'media/com_autotweet/css/style.css?version=' . CAUTOTWEETNG_VERSION);

			Extly::getScriptManager(
				// LoadExtlyAdminMode
				true,
				// Own Jquery Disabled
				false,
				// LoadBootstrap
				true
			);
		}

		return $result;
	}
}
