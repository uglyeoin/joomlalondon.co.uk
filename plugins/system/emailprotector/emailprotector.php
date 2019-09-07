<?php
/**
 * @package         Email Protector
 * @version         4.3.2
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2019 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use RegularLabs\Plugin\System\EmailProtector\Plugin;

// Do not instantiate plugin on install pages
// to prevent installation/update breaking because of potential breaking changes
$input = \Joomla\CMS\Factory::getApplication()->input;
if (in_array($input->get('option'), ['com_installer', 'com_regularlabsmanager']) && $input->get('action') != '')
{
	return;
}

if ( ! is_file(__DIR__ . '/vendor/autoload.php'))
{
	return;
}

require_once __DIR__ . '/vendor/autoload.php';

if (is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php'))
{
	require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';
}
if (\Joomla\CMS\Factory::getApplication()->isClient('site'))
{
	// Include the custom JHtmlEmail class
	$classes = get_declared_classes();
	if ( ! in_array('JHtmlEmail', $classes) && ! in_array('jhtmlemail', $classes))
	{
		require_once __DIR__ . '/jhtmlemail.php';
	}
}

/**
 * Plugin that protects emails
 */
class PlgSystemEmailProtector extends Plugin
{
	public $_alias       = 'emailprotector';
	public $_title       = 'EMAIL_PROTECTOR';
	public $_lang_prefix = 'EP';

	public $_page_types = ['html', 'feed', 'pdf'];

	/*
	 * Below are the events that this plugin uses
	 * All handling is passed along to the parent run method
	 */
	public function onContentPrepare()
	{
		$this->run();
	}

	public function onAfterDispatch()
	{
		$this->run();
	}

	public function onAfterRender()
	{
		$this->run();
	}
}
