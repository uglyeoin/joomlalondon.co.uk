<?php
/**
 * @package         Better Trash
 * @version         1.3.0PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2019 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use RegularLabs\Plugin\System\BetterTrash\Plugin;

// Do not instantiate plugin on install pages
// to prevent installation/update breaking because of potential breaking changes
$input = \Joomla\CMS\Factory::getApplication()->input;
if ($input->get('action') != '' && in_array($input->get('option'), ['com_installer', 'com_regularlabsmanager']))
{
	return;
}

if ( ! is_file(__DIR__ . '/vendor/autoload.php'))
{
	return;
}

require_once __DIR__ . '/vendor/autoload.php';

class PlgSystemBetterTrash extends Plugin
{
	public $_alias       = 'bettertrash';
	public $_title       = 'BETTER_TRASH';
	public $_lang_prefix = 'BP';

	public $_enable_in_frontend    = false;
	public $_enable_in_admin       = true;
	public $_disable_on_components = true;
	public $_page_types            = ['html'];

	/*
	 * Below are the events that this plugin uses
	 * All handling is passed along to the parent run method
	 */
	public function onAfterInitialise()
	{
		$this->run();
	}

	public function onContentAfterSave()
	{
		$this->run();
	}

	public function onContentAfterDelete()
	{
		$this->run();
	}

	public function onContentChangeState()
	{
		$this->run();
	}

	public function onAfterRender()
	{
		$this->run();
	}
}
