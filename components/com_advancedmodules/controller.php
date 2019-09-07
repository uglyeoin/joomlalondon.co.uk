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

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\HTML\HTMLHelper as JHtml;
use Joomla\CMS\MVC\Controller\BaseController as JController;

class AdvancedModulesController extends JController
{
	protected $default_view = 'edit';

	public function display($cachable = false, $urlparams = false)
	{
		return parent::display();
	}

	public function __construct($config = [])
	{
		$this->input = JFactory::getApplication()->input;

		// Modules frontpage Editor Module proxying:
		if ($this->input->get('view') === 'modules' && $this->input->get('layout') === 'modal')
		{
			JHtml::_('stylesheet', 'system/adminlist.css', [], true);
			$config['base_path'] = JPATH_COMPONENT_ADMINISTRATOR;
		}

		parent::__construct($config);
	}
}
