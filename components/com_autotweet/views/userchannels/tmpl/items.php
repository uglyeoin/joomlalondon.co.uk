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

if ($enabledChannel)
{
	$tableId = 'enabledList';
	$tableclass = ' success';
}
else
{
	$tableId = 'pendingList';
	$tableclass = ' info';
}

$tableClass = '';

if ($count == 0)
{
	$tableClass = 'hide';
}

?>

<table
	class="adminlist table table-striped <?php echo
	$tableClass;
	?>"
	id="<?php echo $tableId; ?>">

<?php
	if ($show_navigation)
	{
		include 'head.php';
	}
	else
	{
		include 'head-simple.php';
	}

	if ($show_navigation)
	{
		include 'footer.php';
	}

	echo '<tbody>';

	$i = 0;
	$m = 1;

	if ($enabledChannel)
	{
		$itemsClass = ' enabled-channel';
	}
	else
	{
		$itemsClass = ' pending-channel';
	}

	foreach ($items as $item)
	{
		$m = 1 - $m;

		$checkedout = ($item->checked_out != 0);
		?>
<tr class="row<?php echo $m . $itemsClass . $tableclass; ?>">
		<?php

	if ($enabledChannel)
	{
		echo '<input type="hidden" class="channel_id" value="' . $item->id . '">';
	}
	else
	{
		echo '<input type="hidden" class="channeltype_id" value="' . $item->channeltype_id . '">';
	}

	if (($show_navigation) && ($hasAjaxOrderingSupport !== false))
	{
		?>

		<td class="order nowrap center hidden-phone"><?php
		if ($this->perms->editstate)
		{
			$disableClassName = '';
			$disabledLabel = '';

			if (!$hasAjaxOrderingSupport['saveOrder'])
			{
				$disabledLabel = JText::_('JORDERINGDISABLED');
				$disableClassName = 'inactive tip-top';
			}
			?> <span class="sortable-handler <?php echo $disableClassName?>"
			title="<?php echo $disabledLabel?>" rel="tooltip"> <i
				class="xticon xticon-menu"></i>
		</span> <input type="text" style="display: none" name="order[]"
			size="5" value="<?php echo $item->ordering;?>"
			class="input-mini text-area-order " /> <?php
		}
		else
		{
			?> <span class="sortable-handler inactive"> <i
				class="xticon xticon-menu"></i>
		</span> <?php
		}
		?>
		</td>

		<?php
	}

	if ($show_navigation)
	{
	?>

		<td><?php

			echo JHTML::_('grid.id', $i, $item->id, $checkedout);
		?>
		</td>

	<?php
	}

	?>

	<td class="channel-name"><?php

	// Echo EHtmlGrid::lockedWithIcons($checkedout);

	echo htmlentities($item->name, ENT_COMPAT, 'UTF-8');

	?>
	</td>

		<td class="channel-type">
		<?php echo $item->channeltype_id ? SelectControlHelper::getChanneltypeName($item->channeltype_id) : '&mdash;'; ?>
	</td>

	<?php

	if (($show_navigation) && ($hasAjaxOrderingSupport === false))
	{
		?>

		<td class="order"><span class="order-arrow"><?php
		echo $this->pagination->orderUpIcon($i, true, 'orderup', 'Move Up', $ordering);
		?> </span> <span class="order-arrow"><?php echo $this->pagination->orderDownIcon($i, $count, true, 'orderdown', 'Move Down', $ordering); ?> </span> <?php
		$disabled = $ordering ? '' : 'disabled="disabled"';
		?> <input type="text" name="order[]" size="5"
			value="<?php echo $item->ordering;?>" <?php echo $disabled?>
			class="input-ordering" style="text-align: center" /></td>

		<?php
	}

	?>

	<td>
	<?php

	echo '<span class="auth-action">';

	if ($enabledChannel)
	{
		$authorized = true;

		/*
		echo '<span class="auth-authorized">';

		if ($authorized)
		{
			echo '<span class="label label-success"><i class="xticon xticon-thumbs-o-up"></i></span> ' .
				JText::_('COM_AUTOTWEET_USERCHANNELS_AUTHORIZED');
		}
		else
		{
			echo '<span class="label label-important"><i class="xticon xticon-unlock"></i></span> <a href="#" class="authorize-enabled" title="' .
					JText::_('COM_AUTOTWEET_USERCHANNELS_AUTHORIZE_ITEM') .
					'">' . JText::_('COM_AUTOTWEET_USERCHANNELS_UNAUTHORIZED') . '</a>';
		}

		echo '</span>';

		<span class="auth-authorized">

		<%=
			(message.authorized ?

				'<span class="label label-success"><i class="xticon xticon-thumbs-o-up"></i></span> ' +
					message.lbl_authorized :

				'<span class="label label-important"><i class="xticon xticon-unlock"></i></span> <a href="#" class="authorize-enabled" title="' +
					 message.lbl_authorize_item + '">' +
					 message.lbl_unauthorized + '</a>'
			)
		%>

		</span>

		*/

		echo '<span class="auth-published">';

		if ($item->published)
		{
			echo ' <span class="label label-success"><i class="xticon xticon-check"></i></span> <a class="unpublish" href="#" title="' .
					JText::_('COM_AUTOTWEET_USERCHANNELS_UNPUBLISH_ITEM') .
				'">' . JText::_('COM_AUTOTWEET_USERCHANNELS_PUBLISHED') . '</a>';
		}
		else
		{
			echo ' <span class="label label-important"><i class="xticon xticon-times"></i></span> <a class="publish" href="#" title="' .
					JText::_('COM_AUTOTWEET_USERCHANNELS_PUBLISH_ITEM') .
					'">' . JText::_('COM_AUTOTWEET_USERCHANNELS_UNPUBLISHED') . '</a>';
		}

		echo '</span>';
	}
	else
	{
		echo '<span class="label label-warning"><i class="xticon xticon-unlock"></i></span> <a href="#" class="authorize-pending" title="' .
				JText::_('COM_AUTOTWEET_USERCHANNELS_AUTHORIZE_ITEM') . '">' .
			JText::_('COM_AUTOTWEET_USERCHANNELS_AUTHORIZE_ITEM') . '</a>';
	}

	echo '</span>';

	?>
	</td>

	<?php

	if ($show_navigation)
	{
	?>

	<td>
	<?php

		echo $item->id;

	?>
	</td>

	<?php
	}
	?>

</tr>
<?php
		$i++;
	}

	echo '</tbody>';

?>
</table>
