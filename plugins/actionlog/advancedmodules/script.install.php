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

require_once __DIR__ . '/script.install.helper.php';

class PlgActionlogAdvancedModulesInstallerScript extends PlgActionlogAdvancedModulesInstallerScriptHelper
{
	public $name           = 'ADVANCED_MODULE_MANAGER';
	public $alias          = 'advancedmodules';
	public $extension_type = 'plugin';
	public $plugin_folder  = 'actionlog';

	public function uninstall($adapter)
	{
		$this->uninstallComponent($this->extname);
		$this->uninstallPlugin($this->extname, 'system');
	}
}
