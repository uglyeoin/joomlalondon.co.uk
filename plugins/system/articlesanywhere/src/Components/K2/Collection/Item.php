<?php
/**
 * @package         Articles Anywhere
 * @version         9.3.5PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2019 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\ArticlesAnywhere\Components\K2\Collection;

defined('_JEXEC') or die;

use RegularLabs\Plugin\System\ArticlesAnywhere\Collection\DB;

class Item extends \RegularLabs\Plugin\System\ArticlesAnywhere\Collection\Item
{
	public function getTags()
	{
		return [];
	}

	public function hit()
	{
		return;
	}

	public function getArticle()
	{
		$query = $this->db->getQuery(true)
			->select('*')
			->from($this->config->getTableItems())
			->where($this->db->quoteName('id') . ' = ' . (int) $this->getId());

		return DB::getResults($query, 'loadObject', [], 1);
	}
}
