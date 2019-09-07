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
		<?php
		if ($hasAjaxOrderingSupport !== false)
		{
			?>
		<th width="20px"><?php echo JHtml::_('grid.sort', '<i class="xticon xticon-menu-2"></i>', 'ordering', $this->lists->order_Dir, $this->lists->order, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
		</th>
		<?php
		}
		?>
		<th width="20"><input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
		</th>
		<th><?php echo JHTML::_('grid.sort', 'COM_AUTOTWEET_CHANNELS_FIELD_NAME', 'name', $this->lists->order_Dir, $this->lists->order, 'browse'); ?>
		</th>
		<th width="160"><?php echo JHTML::_('grid.sort', 'LBL_CHANNELS_CHANNELTYPE', 'channeltype_id', $this->lists->order_Dir, $this->lists->order, 'browse'); ?>
		</th>
		<?php
		if ($hasAjaxOrderingSupport === false)
		{
			?>
		<th class="order"><?php echo JHTML::_('grid.sort', 'JFIELD_ORDERING_LABEL', 'ordering', $this->lists->order_Dir, $this->lists->order, 'browse'); ?> <?php
		echo JHTML::_('grid.order', $this->items);
		?>
		</th>
		<?php
		}
		?>
		<th width="80"><?php echo JHTML::_('grid.sort', 'JPUBLISHED', 'published', $this->lists->order_Dir, $this->lists->order, 'browse'); ?>
		</th>

		<th width="80"><?php echo JHTML::_('grid.sort', 'JGLOBAL_FIELD_ID_LABEL', 'id', $this->lists->order_Dir, $this->lists->order, 'browse'); ?>
		</th>
	</tr>
	<tr>
		<?php
		if ($hasAjaxOrderingSupport !== false)
		{
			?>
		<td></td>
		<?php
		}
		?>
		<td></td>
		<td class="form-inline nowrap">
			<div class="input-append">
				<input type="text" name="name" id="name" value="<?php echo $this->escape($this->getModel()->getState('name'));?>" class="input-medium" onchange="document.adminForm.submit();"
					placeholder="<?php echo JText::_('COM_AUTOTWEET_CHANNELS_FIELD_NAME') ?>" />
				<button class="btn" onclick="this.form.submit();">
					<?php echo JText::_('COM_AUTOTWEET_FILTER_SUBMIT'); ?>
					<img src="<?php echo $this->get('blankImage'); ?>" height="20">
				</button>
			</div>

			<a class="xtd-btn-reset"><small><?php echo JText::_('COM_AUTOTWEET_RESET'); ?></small></a>
		</td>
		<td><?php echo SelectControlHelper::channeltypes($this->getModel()->getState('channeltype'), 'channeltype', array('onchange' => 'this.form.submit();', 'class' => 'input-medium'), true); ?>
		</td>
		<?php
		if ($hasAjaxOrderingSupport === false)
		{
			?>
		<td></td>
		<?php
		}
		?>
		<td><?php echo EHtmlSelect::yesNo($this->getModel()->getState('published', 1), 'published', array('onchange-submit' => 'true', 'class' => 'btn-mini')); ?>
		</td>

		<td>
		</td>
	</tr>
</thead>
