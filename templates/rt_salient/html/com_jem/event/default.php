<?php
/**
 * @version 2.1.4
 * @package JEM
 * @copyright (C) 2013-2015 joomlaeventmanager.net
 * @copyright (C) 2005-2009 Christoph Lukes
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');

// Create shortcuts to some parameters.
$params		= $this->item->params;

$images 	= json_decode($this->item->datimage);
$canEdit	= $this->item->params->get('access-edit');
$user		= JFactory::getUser();
$attribs 	= json_decode($this->item->attribs);

JHtml::_('behavior.modal', 'a.flyermodal');
?>
<?php if ($params->get('access-view')) { /* This will show nothings otherwise - ??? */ ?>
<div id="jem" class="event_id<?php echo $this->item->did; ?> jem_event<?php echo $this->pageclass_sfx;?>"
	itemscope="itemscope" itemtype="http://schema.org/Event">
	<div class="buttons">
		<?php
		if ($params->get('event_show_email_icon',1)) {
		echo JemOutput::mailbutton($this->item->slug, 'event', $this->params);
		}
		if ($params->get('event_show_print_icon',1)) {
		echo JemOutput::printbutton($this->print_link, $this->params);
		}
		if ($params->get('event_show_ical_icon',1)) {
		echo JemOutput::icalbutton($this->item->slug, 'event');
		}
		?>
	</div>

	<div class="clr"> </div>

	<?php if ($this->params->get('show_page_heading', 1)) : ?>
		<h1 class="componentheading">
			<?php echo $this->escape($this->params->get('page_heading')); ?>
		</h1>
	<?php endif; ?>

	<div class="clr"> </div>

	<div class="description">
	<!-- DESCRIPTION -->
	<?php if ($params->get('event_show_description','1') && ($this->item->fulltext != '' && $this->item->fulltext != '<br />' || $this->item->introtext != '' && $this->item->introtext != '<br />')) { ?>
	<h2 class="description"><?php echo JText::_('COM_JEM_EVENT_DESCRIPTION'); ?></h2>
	<div class="description event_desc" itemprop="description">

		<?php
		if ($params->get('access-view')) {
			echo $this->item->text;
		}
		/* optional teaser intro text for guests - NOT SUPPORTED YET */
		elseif (0 /*$params->get('event_show_noauth') == true and  $user->get('guest')*/ ) {
			echo $this->item->introtext;
			// Optional link to let them register to see the whole event.
			if ($params->get('event_show_readmore') && $this->item->fulltext != null) {
				$link1 = JRoute::_('index.php?option=com_users&view=login');
				$link = new JUri($link1);
				echo '<p class="readmore">';
					echo '<a href="'.$link.'">';
					if ($params->get('event_alternative_readmore') == false) {
						echo JText::_('COM_JEM_EVENT_REGISTER_TO_READ_MORE');
					} elseif ($readmore = $params->get('alternative_readmore')) {
						echo $readmore;
					}

					if ($params->get('event_show_readmore_title', 0) != 0) {
					    echo JHtml::_('string.truncate', ($this->item->title), $params->get('event_readmore_limit'));
					} elseif ($params->get('event_show_readmore_title', 0) == 0) {
					} else {
						echo JHtml::_('string.truncate', ($this->item->title), $params->get('event_readmore_limit'));
					} ?>
					</a>
				</p>
			<?php
			}
		} /* access_view / show_noauth */
		?>
	</div>
	<?php } ?>
</div>

	<div class="author">

	<!-- AUTHOR -->
		<?php if ($params->get('event_show_author') && !empty($this->item->author)) : ?>
		<span class="createdby"><?php echo JText::_('COM_JEM_EVENT_CREATED_BY_LABEL'); ?>:</span>
		<span class="createdby">
			<?php $author = $this->item->created_by_alias ? $this->item->created_by_alias : $this->item->author; ?>
			<?php if (!empty($this->item->contactid2) && $params->get('event_link_author') == true) :
				$needle = 'index.php?option=com_contact&view=contact&id=' . $this->item->contactid2;
				$menu = JFactory::getApplication()->getMenu();
				$item = $menu->getItems('link', $needle, true);
				$cntlink = !empty($item) ? $needle . '&Itemid=' . $item->id : $needle;
				echo JText::sprintf('COM_JEM_EVENT_CREATED_BY', JHtml::_('link', JRoute::_($cntlink), $author));
			else :
				echo JText::sprintf('COM_JEM_EVENT_CREATED_BY', $author);
			endif;
			?>
		</span>
		<?php endif; ?>
	</span>
</div>




	<!--  Contact -->
	<div class="contact">
	<?php if ($params->get('event_show_contact') && !empty($this->item->conid )) : ?>

	<h2 class="contact"><?php echo JText::_('COM_JEM_CONTACT') ; ?></h2>

	<span class="location floattext">
		<span class="con_name"><?php echo JText::_('COM_JEM_NAME').':'; ?></span>
		<span class="con_name">
		<?php
		$contact = $this->item->conname;
		if ($params->get('event_link_contact') == true) :
			$needle = 'index.php?option=com_contact&view=contact&id=' . $this->item->conid;
			$menu = JFactory::getApplication()->getMenu();
			$item = $menu->getItems('link', $needle, true);
			$cntlink2 = !empty($item) ? $needle . '&Itemid=' . $item->id : $needle;
			echo JText::sprintf('COM_JEM_EVENT_CONTACT', JHtml::_('link', JRoute::_($cntlink2), $contact));
		else :
			echo JText::sprintf('COM_JEM_EVENT_CONTACT', $contact);
		endif;
		?>
		</span>

		<?php if ($this->item->contelephone) : ?>
		<span class="con_telephone"><?php echo JText::_('COM_JEM_TELEPHONE').':'; ?></span>
		<span class="con_telephone">
			<?php echo $this->escape($this->item->contelephone); ?>
		</span>
		<?php endif; ?>
	</span>
	<?php endif ?>

	<?php $this->attachments = $this->item->attachments; ?>
	<?php echo $this->loadTemplate('attachments'); ?>
</div>

<!-- Event -->
<div class="event">
<h2 class="jem">
<?php
	echo JText::_('COM_JEM_EVENT') . JemOutput::recurrenceicon($this->item);
	echo JemOutput::editbutton($this->item, $params, $attribs, $this->allowedtoeditevent, 'editevent');
	?>
</h2>

<?php echo JemOutput::flyer($this->item, $this->dimage, 'event'); ?>

<span class="event_info floattext">
	<?php if ($params->get('event_show_detailstitle',1)) : ?>
	<span class="title"><strong><?php echo JText::_('COM_JEM_TITLE').':'; ?></strong></span>
	<span class="title" itemprop="name"><?php echo $this->escape($this->item->title); ?></span><br>
	<?php
	endif;
	?>
	<span class="when"><strong><?php echo JText::_('COM_JEM_WHEN').':'; ?></strong></span>
	<span class="when">
		<?php
		echo JemOutput::formatLongDateTime($this->item->dates, $this->item->times,$this->item->enddates, $this->item->endtimes);
		echo JemOutput::formatSchemaOrgDateTime($this->item->dates, $this->item->times,$this->item->enddates, $this->item->endtimes);
		?>
	</span><br>
	<?php if ($this->item->locid != 0) : ?>
	<span class="where"><strong><?php echo JText::_('COM_JEM_WHERE').':'; ?></strong></span>
	<span class="where">
		<?php if (($params->get('event_show_detlinkvenue') == 1) && (!empty($this->item->url))) : ?>
			<a target="_blank" href="<?php echo $this->item->url; ?>"><?php echo $this->escape($this->item->venue); ?></a> -
		<?php elseif ($params->get('event_show_detlinkvenue') == 2) : ?>
			<a href="<?php echo JRoute::_(JemHelperRoute::getVenueRoute($this->item->venueslug)); ?>"><?php echo $this->item->venue; ?></a> -
		<?php elseif ($params->get('event_show_detlinkvenue') == 0) :
			echo $this->escape($this->item->venue).' - ';
		endif;

		echo $this->escape($this->item->city).', '.$this->escape($this->item->state); ?>
	</span><br>
	<?php
	endif;
	$n = count($this->categories);
	?>

	<span class="category"><?php echo $n < 2 ? JText::_('COM_JEM_CATEGORY') : JText::_('COM_JEM_CATEGORIES'); ?>:</span>
	<span class="category">
	<?php
	$i = 0;
	foreach ($this->categories as $category) :
		?>
		<a href="<?php echo JRoute::_(JemHelperRoute::getCategoryRoute($category->catslug)); ?>"><?php echo $this->escape($category->catname); ?></a>
		<?php
		$i++;
		if ($i != $n) :
			echo ', ';
		endif;
	endforeach;
	?>
	</span>

	<?php
	for($cr = 1; $cr <= 10; $cr++) {
		$currentRow = $this->item->{'custom'.$cr};
		if (preg_match('%^http(s)?://%', $currentRow)) {
			$currentRow = '<a href="'.$this->escape($currentRow).'" target="_blank">'.$this->escape($currentRow).'</a>';
		}
		if($currentRow) {
		?>
			<span class="custom<?php echo $cr; ?>"><?php echo JText::_('COM_JEM_EVENT_CUSTOM_FIELD'.$cr).':'; ?></span>
			<span class="custom<?php echo $cr; ?>"><?php echo $currentRow; ?></span>
		<?php
		}
	}
	?>

	<?php if ($params->get('event_show_hits')) : ?>
	<span class="hits"><?php echo JText::_('COM_JEM_EVENT_HITS_LABEL'); ?>:</span>
	<span class="hits"><?php echo JText::sprintf('COM_JEM_EVENT_HITS', $this->item->hits); ?></span>
	<?php endif; ?>

	<div class="registration">
		<!-- Registration -->
		<?php if ($this->item->registra == 1) : ?>
			<h2 class="register"><?php echo JText::_('COM_JEM_REGISTRATION').':'; ?></h2>
			<?php echo $this->loadTemplate('attendees'); ?>
		<?php endif; ?>

		<?php if (!empty($this->item->pluginevent->onEventEnd)) : ?>
			<hr>
			<?php echo $this->item->pluginevent->onEventEnd; ?>
		<?php endif; ?>


	</div>
	</div>


	<!--  	Venue  -->
	<div class="venue">
	<?php if ($this->item->locid != 0) : ?>


	<div itemprop="location" itemscope="itemscope" itemtype="http://schema.org/Place">
		<h2 class="location">
			<?php
			echo JText::_('COM_JEM_VENUE') ;
			$itemid = $this->item ? $this->item->id : 0 ;
			echo JemOutput::editbutton($this->item, $params, $attribs, $this->allowedtoeditvenue, 'editvenue');
			?>
		</h2>
		<?php echo JemOutput::flyer($this->item, $this->limage, 'venue'); ?>

		<span class="location">
			<span><?php echo JText::_('COM_JEM_LOCATION').':'; ?></span>
			<span>
				<?php
				echo "<a href='".JRoute::_(JemHelperRoute::getVenueRoute($this->item->venueslug))."'>".$this->escape($this->item->venue)."</a>";
				if (!empty($this->item->url)) :
					echo '&nbsp;-&nbsp;<a target="_blank" href="'.$this->item->url.'">'.JText::_('COM_JEM_WEBSITE').'</a>';
				endif;
				?>
			</span>
		</span>
		<?php if ($params->get('event_show_detailsadress','1')) : ?>
		<span class="location floattext" itemprop="address" itemscope
		    itemtype="http://schema.org/PostalAddress">
			<?php if ($this->item->street) : ?>
			<span class="venue_street" itemprop="streetAddress">
				<?php echo $this->escape($this->item->street); ?>
			</span>
			<?php endif; ?>

			<?php if ($this->item->city) : ?>
			<span class="venue_city" itemprop="addressLocality">
				<?php echo $this->escape($this->item->city);?>
			</span>
			<?php endif; ?>

			<?php if ($this->item->state) : ?>
			<span class="venue_state" itemprop="addressRegion">
				<?php echo $this->escape($this->item->state); ?>
			</span>
			<?php endif; ?>


			<?php if ($this->item->postalCode) : ?>
			<span class="venue_postalCode" itemprop="postalCode">
				<?php echo $this->escape($this->item->postalCode); ?>
			</span>
			<?php endif; ?>

			<?php if ($this->item->country) : ?>
			<span class="venue_country">
				<?php echo $this->item->countryimg ? $this->item->countryimg : $this->item->country; ?>
				<meta itemprop="addressCountry" content="<?php echo $this->item->country; ?>" />
			</span>
			<?php endif; ?>
			<br>&nbsp;
			<?php
			for($cr = 1; $cr <= 10; $cr++) {
				$currentRow = $this->item->{'venue'.$cr};
				if (preg_match('%^http(s)?://%', $currentRow)) {
					$currentRow = '<a href="'.$this->escape($currentRow).'" target="_blank">'.$this->escape($currentRow).'</a>';
				}
				if($currentRow) {
					?>
					<span class="custom<?php echo $cr; ?>"><?php echo JText::_('COM_JEM_VENUE_CUSTOM_FIELD'.$cr).':'; ?></span>
					<span class="custom<?php echo $cr; ?>"><?php echo $currentRow; ?></span>
					<?php
				}
			}
			?>

			<?php if ($params->get('event_show_mapserv')== 1) : ?>
				<?php echo JemOutput::mapicon($this->item,'event',$params); ?>
			<?php endif; ?>
		</span>

			<?php if ($params->get('event_show_mapserv')== 2) : ?>
				<?php echo JemOutput::mapicon($this->item,'event',$params); ?>
			<?php endif; ?>

			<?php if ($params->get('event_show_mapserv')== 3) : ?>
				<input type="hidden" id="latitude" value="<?php echo $this->item->latitude;?>">
				<input type="hidden" id="longitude" value="<?php echo $this->item->longitude;?>">

				<input type="hidden" id="venue" value="<?php echo $this->item->venue;?>">
				<input type="hidden" id="street" value="<?php echo $this->item->street;?>">
				<input type="hidden" id="city" value="<?php echo $this->item->city;?>">
				<input type="hidden" id="state" value="<?php echo $this->item->state;?>">
				<input type="hidden" id="postalCode" value="<?php echo $this->item->postalCode;?>">

				<?php echo JemOutput::mapicon($this->item,'event',$params); ?>
			<?php endif; ?>
		<?php endif; /* event_show_detailsadress */ ?>


		<?php if ($params->get('event_show_locdescription','1') && $this->item->locdescription != ''
		       && $this->item->locdescription != '<br />') : ?>
		<h2 class="location_desc"><?php echo JText::_('COM_JEM_VENUE_DESCRIPTION'); ?></h2>
		<div class="description location_desc" itemprop="description">
			<?php echo $this->item->locdescription; ?>
		</div>
		<?php endif; ?>

		<?php $this->attachments = $this->item->vattachments; ?>
		<?php echo $this->loadTemplate('attachments'); ?>

	</div>
	<?php endif; ?>
</div>
</div> <!-- End eventBlock -->

<?php }
?>
