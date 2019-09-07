<?php
/**
 * @version 2.0.0
 * @package JEM
 * @copyright (C) 2013-2014 joomlaeventmanager.net
 * @copyright (C) 2005-2009 Christoph Lukes
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
defined('_JEXEC') or die;


?>
<div id="jem" class="jem_eventslist<?php echo $this->pageclass_sfx;?>">


	<?php if ($this->params->get('show_page_heading', 1)) : ?>
		<h1 class="componentheading">
			<!-- Delete this nonsense <?php //echo $this->escape($this->params->get('page_heading')); ?>  -->
            Joomla! User Group London - Latest Events
		</h1>
	<?php endif; ?>

	<div class="clr"></div>
    
	<div id="iCal" class="iCal">
		<?php echo JemOutput::icalbutton('', 'eventslist');?> 
		<a class="btn" href="https://www.google.co.uk/maps/place/Malet+Place+Engineering+Building/@51.5231727,-0.1342044,17z/data=!3m1!4b1!4m2!3m1!1s0x48761b2f06b9fa47:0x88fa875309285a86" target="_blank"><i class="fa fa-map-marker"></i> See our venue on a map</a>
		<a class="btn" href="http://www.joomlalondon.co.uk/find-joomla-london"><i class="fa fa-map-signs"></i> Directions to our location</a>

	<?php
			echo JemOutput::submitbutton($this->dellink, $this->params);
			echo JemOutput::archivebutton($this->params, $this->task);
			echo JemOutput::printbutton($this->print_link, $this->params);
		?>
	</div>

	<?php if ($this->params->get('showintrotext')) : ?>
		<div class="description no_space floattext">
			<?php echo $this->params->get('introtext'); ?>
		</div>
	<?php endif; ?>

	<!--table-->

	<form action="<?php echo $this->action; ?>" method="post" name="adminForm" id="adminForm">
		<?php echo $this->loadTemplate('events_table'); ?>

		<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
		<input type="hidden" name="task" value="<?php echo $this->task; ?>" />
		<input type="hidden" name="view" value="eventslist" />
	</form>

	
	
	<?php if ($this->params->get('showfootertext')) : ?>
		<div class="description no_space floattext">
			<?php echo $this->params->get('footertext'); ?>
		</div>
	<?php endif; ?>
	<!--footer-->

	<div class="pagination">
		<?php echo $this->pagination->getPagesLinks(); ?>
	</div>   
</div>