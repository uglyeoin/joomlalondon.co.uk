<?php
/**
 * @package         Snippets
 * @version         6.5.4PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2019 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use RegularLabs\Plugin\System\Snippets\Plugin;

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

/**
 * System Plugin that places a Snippets code block into the text
 */
class PlgSystemSnippets extends Plugin
{
	public $_alias       = 'snippets';
	public $_title       = 'SNIPPETS';
	public $_lang_prefix = 'SNP';

	public $_has_tags = true;

	public function extraChecks()
	{
		if ( ! is_file(JPATH_ADMINISTRATOR . '/components/com_snippets/models/list.php'))
		{
			return false;
		}

		return true;
	}

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

