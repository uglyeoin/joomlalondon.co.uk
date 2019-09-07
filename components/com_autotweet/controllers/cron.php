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
 * AutotweetControllerChannels
 *
 * @package     Extly.Components
 * @subpackage  com_autotweet
 * @since       1.0
 */
class AutotweetControllerCron extends ExtlyController
{
	/**
	 * run.
	 *
	 * Example: http://YOUR_SITE/index.php?option=com_autotweet&view=cron&task=run
	 *
	 * @return	void
	 */
	public function run()
	{
		header('Content-type: text/plain');

		$logger = AutotweetLogger::getInstance();

		$secret_word = EParameter::getComponentParam(CAUTOTWEETNG, 'frontend_secret_word');

		if ((empty($secret_word)) || ($secret_word != JFactory::getApplication()->input->get('key')))
		{
			echo 'Access denied';
			$logger->log(JLog::ERROR, 'Access denied (frontend_secret_word)');

			flush();
			JFactory::getApplication()->close();
		}

		define('AUTOTWEET_CRONJOB_RUNNING', true);

		$now = JFactory::getDate();
		$msg = 'AutotweetControllerCron run: ' . $now->toSql();
		$logger->log(JLog::INFO, $msg);

		@ob_end_clean();
		echo $msg;

		// Disable caching.
		$config = JFactory::getConfig();
		$config->set('caching', 0);
		$config->set('cache_handler', 'file');

		// Starting Indexer.
		$logger->log(JLog::INFO, JText::_('AUTOTWEET_CLI_STARTING_PROCESS'));

		// Remove the script time limit.
		@set_time_limit(0);

		// Initialize the time value
		$this->_time = microtime(true);

		$max_posts = EParameter::getComponentParam(CAUTOTWEETNG, 'max_posts', 1);

		$helper = AutotweetPostHelper::getInstance();
		$helper->postQueuedMessages($max_posts);

		$cronjobHelper = CronjobHelper::getInstance();
		$cronjobHelper->publishPosts();

		$feeds_enabled = EParameter::getComponentParam(CAUTOTWEETNG, 'feeds_enabled', false);

		if ($feeds_enabled)
		{
			$helper = FeedLoaderHelper::getInstance();
			$helper->importFeeds();
		}

		$cronjobHelper->contentPolling();

		// Total reporting.
		$logger->log(JLog::INFO, JText::sprintf('AUTOTWEET_CLI_PROCESS_COMPLETE', round(microtime(true) - $this->_time, 3)));

		flush();
		JFactory::getApplication()->close();
	}
}
