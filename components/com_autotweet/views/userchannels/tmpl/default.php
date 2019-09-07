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

$this->loadHelper('select');

$menu = $this->getModel()->getState('parameters.menu');
$show_navigation = $menu->get('show_navigation');

$hasAjaxOrderingSupport = $this->hasAjaxOrderingSupport();
$ordering = ($this->lists->order == 'ordering');

$frontChannels = $this->get('frontChannels');

?>
<div class="extly user-channels">
	<div class="extly-body">
		<div class="row-fluid">
			<div class="span12">

				<h1>
					<i class="xticon xticon-bullhorn"></i>
					<?php echo JText::_('COM_AUTOTWEET_TITLE_USERCHANNELS'); ?>
				</h1>

				<form name="adminForm" id="adminForm" action="index.php" method="post">

					<div class="text-center">
						<span class="loaderspinner">&nbsp;</span>
					</div>

				    <div id="alert-msg" class="alert alert-error hide">
				    	<span id="error-msg"></span>
				    </div>

					<input type="hidden" name="option" id="option" value="com_autotweet" />
					<input type="hidden" name="view" id="view" value="userchannels" />
					<input type="hidden" name="task" id="task" value="browse" />
					<input type="hidden" name="boxchecked" id="boxchecked" value="0" />
					<input type="hidden" name="hidemainmenu" id="hidemainmenu" value="0" />
					<input type="hidden" name="filter_order" id="filter_order" value="<?php echo $this->lists->order ?>" />
					<input type="hidden" name="filter_order_Dir" id="filter_order_Dir" value="<?php echo $this->lists->order_Dir ?>" />
					<?php
						echo EHtml::renderRoutingTags();

						// Pending channels

						if ($count = count($frontChannels))
						{
							echo '<div class="well"><h2><i class="xticon xticon-unlock"></i></i> ' . JText::_('COM_AUTOTWEET_TITLE_USERCHANNELS_PENDING') . '</h2>';

							$items = &$frontChannels;
							$enabledChannel = false;
							include 'items.php';

							echo '</div>';
						}

						// Enabled channels

						echo '<div class="well"><h2><i class="xticon xticon-link"></i> ' . JText::_('COM_AUTOTWEET_TITLE_USERCHANNELS_ENABLED') . '</h2>';

						$count = count($this->items);

						$items = &$this->items;
						$enabledChannel = true;
						include 'items.php';

						if ($count == 0)
						{
							echo '<p id="no-auth-channels-msg">' . JText::_('COM_AUTOTWEET_TITLE_USERCHANNELS_NOTAUTH') . '</p>';
						}

						echo '</div>';

?>
				</form>

			</div>
		</div>
	</div>
</div>
