<?php
/**
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

defined('_JEXEC') or die;

?>
<thead>
	<tr>
		<th><?php
			echo JHTML::_('grid.sort', 'COM_AUTOTWEET_CHANNELS_FIELD_NAME', 'name', $this->lists->order_Dir, $this->lists->order, 'browse');
		?>
		</th>
		<th><?php
			echo JHTML::_('grid.sort', 'LBL_CHANNELS_CHANNELTYPE', 'channeltype_id', $this->lists->order_Dir, $this->lists->order, 'browse');
		?>
		</th>
		<th><?php
			echo JHTML::_('grid.sort', 'JSTATUS', 'published', $this->lists->order_Dir, $this->lists->order, 'browse');
		?>
		</th>
	</tr>
</thead>
