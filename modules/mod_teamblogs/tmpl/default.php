<?php
/**
 * @package		EasyBlog
 * @copyright	Copyright (C) 2010 Stack Ideas Private Limited. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 *  
 * EasyBlog is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */
defined('_JEXEC') or die('Restricted access');

$menuItemId = modEasyBlogTeamBlogsHelper::_getMenuItemId($params);
?>
<div id="ezblog-teamblog" class="ezb-mod ezblog-teamblog<?php echo $params->get( 'moduleclass_sfx' ) ?>">
<?php if( $teams ){ ?>
	<?php foreach( $teams as $row ) {
		$url = EasyBlogRouter::_( 'index.php?option=com_easyblog&view=teamblog&layout=listings&id=' . $row->id . $menuItemId );
	?>
		<div class="mod-item">
            <a href="<?php echo $url; ?>" class="mod-avatar"><img src="<?php echo $row->getAvatar();?>" width="50" class="avatar" /></a>
            <div class="eztc">
    			<div class="mod-team-name">
    				<a href="<?php echo $url;?>">
    					<b><?php echo $row->title;?></b>
    				</a>
    			</div>
    			<div class="mod-team-members small"><?php echo JText::sprintf( 'MOD_TEAMBLOGS_MEMBERS' , $row->membersCount );?></div>
            </div>
		</div>
	<?php } ?>
<?php } else { ?>
	<div><?php echo JText::_('MOD_TEAMBLOGS_NO_TEAMS'); ?></div>
<?php } ?>
</div>
