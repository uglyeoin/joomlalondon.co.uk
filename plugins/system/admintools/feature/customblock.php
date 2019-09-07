<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

class AtsystemFeatureCustomblock extends AtsystemFeatureAbstract
{
	/**
	 * Shows the Admin Tools custom block message
	 */
	public function onAfterRoute()
	{
		if (!$this->container->platform->getSessionVar('block', false, 'com_admintools'))
		{
			return;
		}

		// This is an underhanded way to short-circuit Joomla!'s internal router.
		$input = JFactory::getApplication()->input;
		$input->set('option', 'com_admintools');
		$input->set('view', 'Blocks');
		$input->set('task', 'browse');

		if (class_exists('JRequest'))
		{
			JRequest::set(array(
				'option' => 'com_admintools',
				'view' => 'blocks'
			), 'get', true);
		}
	}
}
