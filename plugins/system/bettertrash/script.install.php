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

require_once __DIR__ . '/script.install.helper.php';

class PlgSystemBetterTrashInstallerScript extends PlgSystemBetterTrashInstallerScriptHelper
{
	public $name           = 'BETTER_TRASH';
	public $alias          = 'bettertrash';
	public $extension_type = 'plugin';

	public function onAfterInstall($route)
	{
		$this->createTable();
		$this->deleteOldTable();
	}

	public function createTable()
	{
		$query = "CREATE TABLE IF NOT EXISTS `#__bettertrash` (
			`table` char(255) NOT NULL,
			`id` int(11) NOT NULL,
			`date` date NOT NULL,
			KEY  (`date`)
		) DEFAULT CHARSET=utf8;";
		$this->db->setQuery($query);
		$this->db->execute();
	}

	public function deleteOldTable()
	{
		$query = 'DROP TABLE IF EXISTS `#__bettertrash_sefs`;';
		$this->db->setQuery($query);
		$this->db->execute();
	}
}
