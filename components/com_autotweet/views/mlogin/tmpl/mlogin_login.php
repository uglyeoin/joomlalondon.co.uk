<?php

/**
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');

$input = new F0FInput;
$oauth_token = $input->get('oauth_token', null, 'BASE64');

if (!JgOAuthServer::getInstance()->isFirstLegValid($oauth_token))
{
	JFactory::getApplication()->enqueueMessage('Invalid OAuth Access - Login');

	return;
}

?>
<div class="alert login<?php echo $this->pageclass_sfx?>">

	<div class="page-header">
		<h1>
			<?php echo $this->escape($this->params->get('page_heading', JText::_('COM_AUTOTWEET_MLOGIN_VIEW_PAGE_HEADING'))); ?>
		</h1>
	</div>

	<div class="login-description">

	<?php
		echo $this->params->get('login_description', JText::_('COM_AUTOTWEET_MLOGIN_VIEW_LOGIN_DESCRIPTION'));

		if (($this->params->get('login_image') != ''))
		{
	?>
			<img
			src="<?php echo $this->escape($this->params->get('login_image')); ?>"
			class="login-image"
			alt="<?php echo JTEXT::_('COM_AUTOTWEET_MLOGIN_IMAGE_ALT')?>" />
	<?php
		}
	?>

	<p>
		<img src="media/lib_extly/images/ajax-loader.gif" class="loaderspinner hide">
	</p>

	<form
		action="<?php echo JRoute::_('index.php?option=com_autotweet&task=login&view=mlogin'); ?>"
		method="post" class="form-validate form-horizontal well">

		<fieldset>
			<?php

			foreach ($this->form->getFieldset('credentials') as $field)
			{
				if (!$field->hidden)
				{
			?>
					<div class="control-group">
				<div class="control-label">
							<?php echo $field->label; ?>
						</div>
				<div class="controls">
							<?php echo $field->input; ?>
						</div>
			</div>
				<?php
				}
			}

			if ($this->tfa)
			{
		?>
				<div class="control-group">
				<div class="control-label">
						<?php echo $this->form->getField('secretkey')->label; ?>
					</div>
				<div class="controls">
						<?php echo $this->form->getField('secretkey')->input; ?>
					</div>
			</div>
		<?php
			}
		?>
			<div class="control-group">
				<div class="controls">
					<button type="submit" class="btn btn-primary" onclick="jQuery('.loaderspinner').removeClass('hide');">
						<?php echo JText::_('JLOGIN'); ?>
					</button>
				</div>
			</div>

			<input type="hidden" name="oauth_token"
				value="<?php

				echo $oauth_token;

				?>" />

			<?php echo JHtml::_('form.token'); ?>
		</fieldset>
	</form>
</div>
