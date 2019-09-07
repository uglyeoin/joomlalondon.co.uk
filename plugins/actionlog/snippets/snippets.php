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

if ( ! is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php'))
{
	return;
}

require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';

/**
 * Plugin that logs User Actions
 */
class PlgActionlogSnippets
	extends \RegularLabs\Library\ActionLogPlugin
{
	public $name      = 'SNIPPETS';
	public $alias     = 'snippets';

	public function __construct(&$subject, array $config = [])
	{
		parent::__construct($subject, $config);

		$this->items = [
			'item' => (object) [
				'title' => 'SNP_ITEM',
			],
		];
	}
}
