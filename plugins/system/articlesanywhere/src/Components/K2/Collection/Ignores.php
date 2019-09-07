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

use JDatabaseQuery;
use Joomla\CMS\Factory as JFactory;

class Ignores extends \RegularLabs\Plugin\System\ArticlesAnywhere\Collection\Ignores
{
	protected function setState(JDatabaseQuery $query, $table = 'items', $group = '')
	{
		$ignore = $this->getState($group);

		if ($ignore)
		{
			return;
		}

		$state = $this->config->get($table . '_state', false) ?: 'published';

		$query->where($this->db->quoteName($table . '.' . $state) . ' = 1');

		if (in_array($table, ['items', 'categories']))
		{
			$query->where($this->db->quoteName($table . '.trash') . ' = 0');
		}

		if ($table == 'items')
		{
			$nowDate  = $this->db->quote(JFactory::getDate()->toSql());
			$nullDate = $this->db->quote($this->db->getNullDate());

			$query->where('( ' . $this->db->quoteName($table . '.publish_up') . ' <= ' . $nowDate . ' )')
				->where('( ' . $this->db->quoteName($table . '.publish_down') . ' = ' . $nullDate
					. ' OR ' . $this->db->quoteName($table . '.publish_down') . ' > ' . $nowDate . ' )');
		}
	}

}
