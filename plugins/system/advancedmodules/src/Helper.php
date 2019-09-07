<?php
/**
 * @package         Advanced Module Manager
 * @version         7.11.0PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2019 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\AdvancedModules;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use RegularLabs\Library\Document as RL_Document;

class Helper
{
	public function onAfterInitialise()
	{
		if (Params::get()->initialise_event != 'onAfterRoute')
		{
			$this->initialise();
		}
	}

	public function onAfterRoute()
	{
		if ( ! RL_Document::isAdmin())
		{
			Document::loadFrontEditScript();
		}

		if (Params::get()->initialise_event == 'onAfterRoute')
		{
			$this->initialise();
		}
	}

	public function initialise()
	{
		if (RL_Document::isAdmin())
		{
			return;
		}

		ModuleHelper::registerEvents();
	}

	public function onAfterRender()
	{
		Document::replaceLinks();

		if ( ! RL_Document::isAdmin())
		{
			$html = JFactory::getApplication()->getBody();
			Clean::cleanLeftoverJunk($html);
			JFactory::getApplication()->setBody($html);
		}
	}
}
