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
 * AutotweetViewUserChannels
 *
 * @package     Extly.Components
 * @subpackage  com_autotweet
 * @since       1.0
 */
class AutotweetViewUserChannels extends AutoTweetDefaultView
{
	/**
	 * onBrowse.
	 *
	 * @param   string  $tpl  Param
	 *
	 * @return	void
	 */
	protected function onBrowse($tpl = null)
	{
		Extly::loadAwesome();
		Extly::initApp(CAUTOTWEETNG_VERSION);

		$file = EHtml::getRelativeFile('js', 'com_autotweet/userchannel.min.js');

		if ($file)
		{
			$paths = array(
							'text' => Extly::JS_LIB . 'require/text.min'
			);

			$dependencies = array();
			$dependencies['userchannel'] = array('extlycore', 'text');
			Extly::initApp(CAUTOTWEETNG_VERSION, $file, $dependencies, $paths);
		}

		return parent::onBrowse($tpl);
	}

	/**
	 * Executes before rendering a generic page, default to actions necessary
	 * for the Browse task.
	 *
	 * @param   string  $tpl  Subtemplate to use
	 *
	 * @return  boolean  Return true to allow rendering of the page
	 */
	protected function onDisplay($tpl = null)
	{
		$result = parent::onDisplay($tpl);

		$channels = F0FModel::getTmpInstance('Channels', 'AutotweetModel');

		$ids = $channels->getChannelTypes($this->items);

		$channels->setState('frontendchannel', 1);
		$channels->setState('exclude_channeltypes', $ids);
		$frontChannels = $channels->getItemList(true);

		$this->assign('frontChannels', $frontChannels);

		return $result;
	}
}
