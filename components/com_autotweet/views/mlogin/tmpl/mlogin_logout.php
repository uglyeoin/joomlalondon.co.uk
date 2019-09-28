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

use XTP_BUILD\Extly\Infrastructure\Service\Cms\Joomla\ScriptHelper;

$input = new F0FInput;
$oauth_token = $input->get('oauth_token', null, 'BASE64');

if (!JgOAuthServer::getInstance()->isFirstLegValid($oauth_token))
{
	JFactory::getApplication()->enqueueMessage('Invalid OAuth Access - Login');

	return;
}

$oauth_token = base64_decode($oauth_token);
$callback = JgOAuthServer::getInstance()->getVerifierCallback($oauth_token);

ScriptHelper::addScriptDeclaration('window.location=\'' . htmlentities($callback) . '\';');

?>
<div class="logout<?php echo $this->pageclass_sfx?>">
	<?php

	if ($this->params->get('show_page_heading'))
	{
	?>
	<div class="page-header">
		<h1>
			<?php echo $this->escape($this->params->get('page_heading')); ?>
		</h1>
	</div>
	<?php
	}

	if (($this->params->get('logoutdescription_show') == 1 && str_replace(' ', '', $this->params->get('logout_description')) != '')|| $this->params->get('logout_image') != '')
	{
	?>
	<div class="logout-description">
	<?php
	}

	if ($this->params->get('logoutdescription_show') == 1)
	{
		echo $this->params->get('logout_description');
	}

	if (($this->params->get('logout_image') != ''))
	{
	?>
			<img
			src="<?php echo $this->escape($this->params->get('logout_image')); ?>"
			class="thumbnail pull-right logout-image"
			alt="<?php echo JTEXT::_('COM_AUTOTWEET_LOGOUT_IMAGE_ALT')?>" />
	<?php
	}

	if (($this->params->get('logoutdescription_show') == 1 && str_replace(' ', '', $this->params->get('logout_description')) != '')|| $this->params->get('logout_image') != '')
	{
?>
	</div>
<?php
	}
?>

	<form
		action="<?php echo JRoute::_('index.php?option=com_autotweet&task=logout&view=mlogin'); ?>"
		method="post" class="form-horizontal well">
		<div class="control-group">
			<div class="controls">
				<button type="submit" class="btn btn-primary">
					<span class="icon-arrow-left icon-white"></span> <?php echo JText::_('JLOGOUT'); ?></button>

<?php
				if ($callback)
				{
					echo '<a class="btn" onclick="window.location=\'' . htmlentities($callback) . '\'"><span class="icon-arrow-right"></span> Continue</a>';
				}
?>
			</div>
		</div>
		<input type="hidden" name="return"
			value="<?php echo base64_encode($this->params->get('logout_redirect_url', $this->form->getValue('return'))); ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
