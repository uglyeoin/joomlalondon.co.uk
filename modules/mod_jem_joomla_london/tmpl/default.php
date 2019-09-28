<?php
/**
 * @version 2.2.2
 * @package JEM
 * @subpackage JEM joomla_london Module
 * @copyright (C) 2013-2017 joomlaeventmanager.net
 * @copyright (C) 2005-2009 Christoph Lukes
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

JHtml::_('behavior.modal', 'a.flyermodal');
?>


<!-- open and close button

<a class="ui-btn ui-menu-close">Close <i class="fa fa-close" aria-label="Close dialog"></i></a>

CSS is already done

-->

<div class="jemmodulejoomla_london<?php echo $params->get('moduleclass_sfx')?>" id="jemmodule_joomla_london">

<?php if (count($list)) : ?>
		<?php foreach ($list as $item) : ?>
			<div class="<?php echo $item->module; ?>">
				<div class="jem-initial-details">
					<div class="jem-event-image">
						<!-- IMAGE or Attractive looking "this month you are going to get value because X".  e.g. speaker. topic.  etc. -->
						<a href="<?php echo $item->eventlink; ?>">
							<img src="<?php  echo $item->eventimage; ?>" alt="<?php echo $item->eventtitle; ?>">
						</a>
					</div>
					<div class="jem-event-countdown">
						<?php $document->loadRenderer('modules')->render("JemCountdownTimer"); ?>
					</div>		
					<div class="jem-date">
						<?php  echo $item->dateinfo; ?>
					</div>
					<div class="jem-date">
						<a href="/registration-form/view/form" class="button">Register to Attend</a> 
						<a href="/find-joomla-london" target="_blank" class="button">Get Directions</a> 
						<a href="<?php echo $item->eventlink; ?>" class="button">More Information</a>
					</div>		
				</div>
				<div class="jem-popout">
					<div class="jem-registration-form">
						<?php $document->loadRenderer('modules')->render("JemRegistrationForm"); ?>
					</div>
					<div class="jem-more-information">
						<?php  echo $item->eventdescription; ?>
					</div>
				</div>
			</div>		
		<?php endforeach; ?>
	</table>
<?php else : ?>
	<?php echo JText::_('MOD_JEM_WIDE_NO_EVENTS'); ?>
<?php endif; ?>
</div>