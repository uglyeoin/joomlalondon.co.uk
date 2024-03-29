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

use Joomla\CMS\Access\Exception\NotAllowed as JAccessExceptionNotallowed;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\MVC\Controller\BaseController as JController;
use Joomla\CMS\Plugin\PluginHelper as JPluginHelper;
use RegularLabs\Library\Language as RL_Language;

$app = JFactory::getApplication();

if ( ! JFactory::getUser()->authorise('module.edit.frontend', 'com_modules.module.' . $app->input->get('id'))
	&& ! JFactory::getUser()->authorise('module.edit.frontend', 'com_modules')
)
{
	throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
}

JFactory::getLanguage()->load('com_modules', JPATH_ADMINISTRATOR);
JFactory::getLanguage()->load('com_advancedmodules', JPATH_ADMINISTRATOR . '/components/com_advancedmodules');

jimport('joomla.filesystem.file');

// return if Regular Labs Library plugin is not installed
if (
	! is_file(JPATH_PLUGINS . '/system/regularlabs/regularlabs.xml')
	|| ! is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php')
)
{
	$msg = JText::_('AMM_REGULAR_LABS_LIBRARY_NOT_INSTALLED')
		. ' ' . JText::sprintf('AMM_EXTENSION_CAN_NOT_FUNCTION', JText::_('COM_ADVANCEDMODULES'));
	JFactory::getApplication()->enqueueMessage($msg, 'error');

	return;
}

// give notice if Regular Labs Library plugin is not enabled
if ( ! JPluginHelper::isEnabled('system', 'regularlabs'))
{
	$msg = JText::_('AMM_REGULAR_LABS_LIBRARY_NOT_ENABLED')
		. ' ' . JText::sprintf('AMM_EXTENSION_CAN_NOT_FUNCTION', JText::_('COM_ADVANCEDMODULES'));
	JFactory::getApplication()->enqueueMessage($msg, 'notice');
}

require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';

RL_Language::load('plg_system_regularlabs');
// Load admin main core language strings
RL_Language::load('', JPATH_ADMINISTRATOR);

// Tell the browser not to cache this page.
$app->setHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT', true);

$config = [];
if ($app->input->get('task') === 'module.orderPosition')
{
	$config['base_path'] = JPATH_COMPONENT_ADMINISTRATOR;
}

$controller = JController::getInstance('AdvancedModules', $config);
$controller->execute($app->input->get('task'));
$controller->redirect();
