<?php
/**
 * @version 2.1.4
* @package JEM
* @subpackage JEM Banner Module
* @copyright (C) 2014-2015 joomlaeventmanager.net
* @copyright (C) 2005-2009 Christoph Lukes
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
defined('_JEXEC') or die;

$datemethod      = (int)$params->get('datemethod', 1);
$showcalendar    = (int)$params->get('showcalendar', 1);
$showflyer       = (int)$params->get('showflyer', 1);
$flyer_link_type = (int)$params->get('flyer_link_type', 0);

if ($flyer_link_type == 1) {
	JHtml::_('behavior.modal', 'a.flyermodal');
	$modal = 'flyermodal';
} elseif ($flyer_link_type == 0) {
	$modal = 'notmodal';
} else {
	$modal = '';
}
?>

<div id="jemmodulebanner">
<?php ?>
	<div class="eventset" summary="mod_jem_banner">
	<?php $i = count($list); ?>
	<?php foreach ($list as $item) : ?>

		<h2 class="event-title">
		<?php if ($item->eventlink) : ?>
			<a href="<?php echo $item->eventlink; ?>" title="<?php echo $item->fulltitle; ?>"><?php echo $item->title; ?></a>
		<?php else : ?>
			<?php echo $item->title; ?>
		<?php endif; ?>
		</h2>

		<div class="eventWrap">
			<?php if ($showcalendar == 1) :?>
			<div class="calWrap col-md-3">
				<div class="calendar<?php echo '-'.$item->colorclass; ?>"
				     title="<?php echo strip_tags($item->dateinfo); ?>"
					<?php if (!empty($item->color)) : ?>
				     style="background-color: <?php echo $item->color; ?>"
					<?php endif; ?>
				>
					<div class="monthbanner">
						<?php echo $item->startdate['month']; ?>
					</div>
					<div class="daybanner">
						<?php echo $item->startdate['weekday']; ?>
					</div>
					<div class="daynumbanner">
						<?php echo $item->startdate['day']; ?>
					</div>
				</div>
				<?php /* Datum und Zeitangabe:
		       *  showcalendar 1, datemethod 1 : date inside calendar image + time
		       *  showcalendar 1, datemethod 2 : date inside calendar image + relative date + time
		       *  showcalendar 0, datemethod 1 : no calendar image, date + time
		       *  showcalendar 0, datemethod 2 : no calendar image, relative date + time
		       */
				 ?>
				<?php /* wenn kein Kalenderblatt angezeigt wird */ ?>
				<?php if ($showcalendar == 0) : ?>
					<?php if ($item->date && $datemethod == 2) :?>
						<div class="date" title="<?php echo strip_tags($item->dateinfo); ?>">
							<?php echo trim($item->date); ?> <!-- BW Modded -->
						</div>
					<?php endif; ?>
					<?php if ($item->date && $datemethod == 1) :?>
						<div class="date" title="<?php echo strip_tags($item->dateinfo); ?>">
							<?php echo trim($item->date); ?> <!-- BW Modded -->
						</div>
					<?php endif; ?>
					<?php if ($item->time && $datemethod == 1) :?>
						<div class="time" title="<?php echo strip_tags($item->dateinfo); ?>">
							<?php echo trim($item->time); ?> <!-- BW Modded -->
						</div>
					<?php endif; ?>

				<?php /* wenn Kalenderblatt angezeigt wird */ ?>
				<?php else : ?>
					<?php /* wenn Zeitdifferenz angezeigt werden soll */ ?>
					<?php if ($item->date && $datemethod == 2) : ?>
						<div class="date" title="<?php echo strip_tags($item->dateinfo); ?>">
							<?php echo trim($item->date); ?> <!-- BW Modded -->
						</div>
					<?php endif; ?>

					<?php /* wenn Datum angezeigt werden soll */ ?>
					<?php if ($item->time && $datemethod == 1) :?>
						<?php /* es muss nur noch die Zeit angezeigt werden (da Datum auf Kalenderblatt schon angezeigt) */ ?>
						<div class="time" title="<?php echo strip_tags($item->dateinfo); ?>">
							<?php echo trim($item->time); ?> <!-- BW Modded -->
						</div>
					<?php endif; ?>
				<?php endif; ?>

					<div class="clr"></div>
				</div>
 			<?php endif; ?>

			<?php if (($showflyer == 1) AND
			          (($item->eventimage)!=str_replace("jpg","",($item->eventimage)) OR
					   ($item->eventimage)!=str_replace("gif","",($item->eventimage)) OR
					   ($item->eventimage)!=str_replace("png","",($item->eventimage)))) : ?>
				<div class="banner-jem">
					<div>
						<?php $class = ($showcalendar == 1) ? 'image-preview' : 'image-preview2'; ?>
						<a href="<?php echo ($flyer_link_type == 2) ? $item->eventlink : $item->eventimageorig; ?>" class="<?php echo $modal;?>"
						   title="<?php echo ($flyer_link_type == 2) ? $item->fulltitle : JText::_('MOD_JEM_BANNER_CLICK_TO_ENLARGE'); ?> ">
							<img class="float_right <?php echo $class; ?>" src="<?php echo $item->eventimageorig; ?>" alt="<?php echo $item->title; ?>" />
						</a>
					</div>
				</div>
				<!-- BW Rem'd
				<?php // else /* showflyer == 0 or no image */ : ?>
				<div>
					<div class="banner-jem">
					</div>
				</div> -->
			<?php endif; ?>

			<?php if ($params->get('showdesc', 1) == 1) :?>
			<div class="desc col-md-9">
				<?php echo trim($item->eventdescription); ?> <!-- BW Modded -->
				<?php if (isset($item->link) && $item->readmore != 0 && $params->get('readmore')) :
					echo '<div class="btnWrap"><a class="readmore btn" href="'.$item->link.'">'.$item->linkText.'</a></div>';
				endif; ?>
			</div>
			<?php endif; ?>
		</div>

		<div class="clr"></div>

		

		<?php /*venue*/ ?>
		<?php if ($params->get('showvenue', 1) == 1) :?>
			<div class="venue-title">
			<?php if ($item->venuelink) : ?>
				<a href="<?php echo $item->venuelink; ?>" title="<?php echo $item->venue; ?>"><?php echo $item->venue; ?></a>
			<?php else : ?>
				<?php echo $item->venue; ?>
			<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php /*category*/ ?>
		<?php if ($params->get('showcategory', 1) == 1) :?>
			<div class="category">
				<?php echo $item->catname; ?>
			</div>
		<?php endif; ?>

		<div class="clr"></div>

		<?php if (--$i > 0) : /* no hr after last entry */ ?>
		<div class="hr"><hr /></div>
		<?php endif; ?>
	<?php endforeach; ?>
	</div>
</div>