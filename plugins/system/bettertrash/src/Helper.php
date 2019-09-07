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

namespace RegularLabs\Plugin\System\BetterTrash;

defined('_JEXEC') or die;

/**
 * Plugin that replaces stuff
 */
class Helper
{
	private $storage;
	private $buttons;
	private $trash;

	function __construct()
	{
		$this->trash   = new Trash;
		$this->storage = new Storage;
		$this->buttons = new Buttons;
	}

	public function onAfterInitialise()
	{
		$this->trash->remove();
	}

	public function onContentAfterSave($context, $item, $isNew)
	{
		$this->storage->updateItem($item, $isNew, $context);
	}

	public function onContentAfterDelete($context, $item)
	{
		$this->storage->removeItem($item, $context);
	}

	public function onContentChangeState($context, $ids, $state)
	{
		$this->storage->updateList($ids, $state, $context);
	}

	public function onAfterRender()
	{
		$this->buttons->change();
	}
}
