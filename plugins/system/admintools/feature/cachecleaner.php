<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use FOF30\Date\Date;

defined('_JEXEC') or die;

class AtsystemFeatureCachecleaner extends AtsystemFeatureAbstract
{
	protected $loadOrder = 630;

	/**
	 * Is this feature enabled?
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		return ($this->params->get('cachecleaner', 0) == 1);
	}

	public function onAfterInitialise()
	{
		$minutes = (int)$this->params->get('cache_freq', 0);

		if ($minutes <= 0)
		{
			return;
		}

		$lastJob = $this->getTimestamp('cache_clean');
		$nextJob = $lastJob + $minutes * 60;

		JLoader::import('joomla.utilities.date');
		$now = new Date();

		if ($now->toUnix() >= $nextJob)
		{
			$this->setTimestamp('cache_clean');
			$this->purgeCache();
		}
	}

	/**
	 * Completely purges the cache
	 */
	private function purgeCache()
	{
		JLoader::import('joomla.application.helper');
		JLoader::import('joomla.cms.application.helper');

		// Site client
		$client = class_exists('Joomla\\CMS\\Application\\ApplicationHelper') ? \Joomla\CMS\Application\ApplicationHelper::getClientInfo(0) : JApplicationHelper::getClientInfo(0);

		$er = @error_reporting(0);
		$cache = JFactory::getCache('');
		$cache->clean('sillylongnamewhichcantexistunlessyouareacompletelyparanoiddeveloperinwhichcaseyoushouldnotbewritingsoftwareokay', 'notgroup');
		@error_reporting($er);
	}
}
