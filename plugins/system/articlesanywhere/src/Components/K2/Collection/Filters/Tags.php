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

namespace RegularLabs\Plugin\System\ArticlesAnywhere\Components\K2\Collection\Filters;

defined('_JEXEC') or die;

use RegularLabs\Library\DB as RL_DB;

class Tags extends \RegularLabs\Plugin\System\ArticlesAnywhere\Collection\Filters\Tags
{
	protected function getIdsQuery()
	{
		$tag_ids = $this->getTagIds();

		return $this->db->getQuery(true)
			->select($this->db->quoteName('itemID'))
			->from($this->db->quoteName('#__k2_tags_xref'))
			->where($this->db->quoteName('tagID') . RL_DB::in($tag_ids));
	}
}
