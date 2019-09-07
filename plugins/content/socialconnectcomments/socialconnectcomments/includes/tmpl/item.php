<?php
/**
 * @version     1.8.x
 * @package     SocialConnect
 * @author      JoomlaWorks http://www.joomlaworks.net
 * @copyright   Copyright (c) 2006 - 2016 JoomlaWorks Ltd. All rights reserved.
 * @license     http://www.joomlaworks.net/license
 */

// no direct access
defined('_JEXEC') or die; ?>
<div class="socialConnectCommentsCounter">
	<div class="socialConnect<?php echo $this->service; ?>CommentsCounter">
		<?php if($this->service == 'Facebook'): ?>
			<a href="<?php echo $this->itemURL; ?>#socialConnect<?php echo $this->service; ?>CommentsAnchor"><?php echo $this->counter; ?> <?php echo JText::_('JW_SC_COMMENTS'); ?></a>
		<?php else: ?>
			<?php echo $this->counter; ?>
		<?php endif; ?>
	</div>
	<div class="clr"></div>
</div>

<?php echo $this->row->text; ?>

<div class="socialConnectCommentsBlock">
	<a name="socialConnect<?php echo $this->service; ?>CommentsAnchor" id="socialConnect<?php echo $this->service; ?>CommentsAnchor"></a>
	<?php echo $this->comments; ?>
</div>
<div class="clr"></div>
