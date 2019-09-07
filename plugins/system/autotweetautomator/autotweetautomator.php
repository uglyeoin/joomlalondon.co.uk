<?php

/**
 * @package     Extly.Plugins
 * @subpackage  autotweetautomator - Plugin AutoTweet NG Automator-Plugin
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2018 Extly, CB. All rights reserved.
 * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

defined('_JEXEC') or die;

include_once JPATH_ADMINISTRATOR . '/components/com_autotweet/api/autotweetapi.php';

/**
 * PlgSystemAutotweetAutomator class.
 *
 * @package     Extly.Components
 * @subpackage  com_autotweet
 * @since       1.0
 */
class PlgSystemAutotweetAutomator extends JPlugin
{
	protected $cron_enabled = false;

	protected $max_posts = 1;

	protected $interval = 180;

	protected $detect_bots = 0;

	protected $crawlers = 'Google|Rambler|Yahoo|accoona|ASPSeek|Lycos|Scooter|AltaVista|eStyle|Scrubby|Yandex|Speedy|Ezooms|ichiro|Minisearch|Gist|TweetedTimes|Facebook|Twitter';

	protected $crawler_patterns = 'crawl|bot|spider|hunter|checker|discovery|Java';

	protected $additional_crawlers = '';

	protected $blocked_ips = '';

	/**
	 * plgSystemAutotweetAutomator.
	 *
	 * @param   string  &$subject  Params
	 * @param   array   $params    Params
	 */
	public function __construct(&$subject, $params)
	{
		parent::__construct($subject, $params);

		$pluginParams = $this->params;

		$this->max_posts = (int) $pluginParams->get('max_posts', 1);
		$this->interval = (int) $pluginParams->get('interval', 180);
		$this->detect_bots = (int) $pluginParams->get('detect_bots', 0);
		$this->additional_crawlers = $pluginParams->get('crawlers', '');
		$this->blocked_ips = $pluginParams->get('blocked_ips', '');

		// Correct value if value is under the minimum
		if ($this->interval < 180)
		{
			$this->interval = 180;
		}

		// Load component language file for use with plugin
		$jlang = JFactory::getLanguage();
		$jlang->load('com_autotweet');
	}

	/**
	 * Checks for new events in the database (no triggers).
	 *
	 * @return	void
	 */
	private function _onAfterRender()
	{
		$app = JFactory::getApplication();

		if ($app->isAdmin())
		{
			return;
		}

		$option = $app->input->get('option');
		$task = $app->input->get('task');

		if (($option == 'com_autotweet') && ($task == 'route'))
		{
			return;
		}

		$this->cron_enabled = EParameter::getComponentParam(CAUTOTWEETNG, 'cron_enabled', false);

		if ($this->cron_enabled)
		{
			return;
		}

		$automators = F0FModel::getTmpInstance('Automators', 'AutoTweetModel');

		if (!$automators->lastRunCheck('automator', $this->interval))
		{
			return;
		}

		$logger = AutotweetLogger::getInstance();

		// Bot/crawler detection
		$http_user_agent = (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null);
		$remote_addr = (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null);

		if ((0 < $this->detect_bots) && ($this->detectCrawlerByAgent($http_user_agent) || $this->detectCrawlerByIP($remote_addr)))
		{
			$logger->log(JLog::WARNING, 'AutoTweet NG Automator-Plugin - crawler detected. IP: ' . $remote_addr . ', Agent: ' . $http_user_agent);

			return;
		}

		$logger->log(JLog::INFO, 'AutoTweet NG Automator-Plugin - executed - IP: ' . $remote_addr . ', Agent: ' . $http_user_agent);

		define('AUTOTWEET_AUTOMATOR_RUNNING', true);

		$helper = AutotweetPostHelper::getInstance();
		$helper->postQueuedMessages($this->max_posts);

		$feeds_enabled = EParameter::getComponentParam(CAUTOTWEETNG, 'feeds_enabled', false);

		if ($feeds_enabled)
		{
			$helper = FeedLoaderHelper::getInstance();
			$helper->importFeeds();
		}
	}

	/**
	 * detectCrawlerByAgent
	 *
	 * @param   string  $userAgent  Param.
	 *
	 * @return	string
	 */
	private function detectCrawlerByAgent($userAgent)
	{
		$crawlers = $this->crawlers . '|' . $this->crawler_patterns;
		$additional_crawlers = trim($this->additional_crawlers);

		if (!empty($additional_crawlers))
		{
			$c = str_replace(',', '|', $additional_crawlers);
			$crawlers = $crawlers . '|' . $c;
		}

		return (preg_match("/$crawlers/i", $userAgent) > 0);
	}

	/**
	 * detectCrawlerByIP
	 *
	 * @param   string  $userIP  Param.
	 *
	 * @return	boolean
	 */
	private function detectCrawlerByIP($userIP)
	{
		$result = false;
		$blocked_ips = trim($this->blocked_ips);

		if (!empty($blocked_ips))
		{
			$ip_list = str_replace(',', '|', $blocked_ips);
			$result = (preg_match("/$ip_list/", $userIP) > 0);
		}

		return $result;
	}

	/**
	 * onAfterRender
	 *
	 * @return	void
	 */
	public function onAfterRender()
	{
		if ((class_exists('Extly')) && (Extly::hasApp()))
		{
			$app = JFactory::getApplication();

			// Get the response body .... an additional check for J! 3.0.0
			if (method_exists($app, 'getBody'))
			{
				$body = $app->getBody();
			}
			else
			{
				$body = JResponse::getBody();
			}

			Extly::insertDependencyManager($body);

			if (method_exists($app, 'setBody'))
			{
				$app->setBody($body);
			}
			else
			{
				JResponse::setBody($body);
			}
		}

		$this->_onAfterRender();
	}
}
