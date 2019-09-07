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
?>
<div class="searchblogs<?php echo $params->get( 'moduleclass_sfx' ) ?>">
<form name="search-blogs" action="<?php echo EasyBlogRouter::_( 'index.php?option=com_easyblog&view=search' );?>" method="post">
	<input type="text" name="query" id="search-blogs" class="input" />
	<button class="ui-button button"><?php echo JText::_( 'COM_EASYBLOG_SEARCH' );?></button>
</form>
</div>
