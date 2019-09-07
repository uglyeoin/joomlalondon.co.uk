<?php
/**
 * @package         Better Preview
 * @version         6.2.2PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2019 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use RegularLabs\Plugin\System\BetterPreview\Plugin;

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

class PlgSystemBetterPreview extends Plugin
{
	public $_alias       = 'betterpreview';
	public $_title       = 'BETTER_PREVIEW';
	public $_lang_prefix = 'BP';

	public $_enable_in_admin = true;
	public $_page_types      = ['html'];

	/*
	 * Below are the events that this plugin uses
	 * All handling is passed along to the parent run method
	 */
	public function onAfterRoute()
	{
		$this->run();
	}

	public function onContentPrepare()
	{
		$this->run();
	}

	public function onAfterRender()
	{
		$this->run();
	}
}
