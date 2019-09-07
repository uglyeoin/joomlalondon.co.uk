<?php
/**
 * @version 2.2.3
 * @package JEM
 * @copyright (C) 2013-2017 joomlaeventmanager.net
 * @copyright (C) 2005-2009 Christoph Lukes
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

$class = ' class="first"';
?>

<?php /*
<div class="subcategories">
<?php //echo JText::_('COM_JEM_SUBCATEGORIES'); ?>
</div>
 */ ?>

<?php if (array_key_exists($this->category->id, $this->children) && (count($this->children[$this->category->id]) > 0)) : ?>
	<?php
	$lastid = 0;
	foreach ($this->children[$this->category->id] as $id => $child) :
		if ($this->params->get('showemptychilds', 1) || ($child->getNumItems(true) > 0)) :
			$lastid = $id;
		endif;
	endforeach;
	?>

	<ul>
	<?php foreach ($this->children[$this->category->id] as $id => $child) : ?>

		<?php
		// Note: We don't skip empty subcategories if they have at least one non-empty subsubcategory.
		if (!$this->params->get('showemptychilds', 1) && ($child->getNumItems(true) <= 0)) :
			continue; // skip this subcat
		endif;

		if ($id == $lastid) :
			$class = ' class="last"';
		endif;
		?>

		<li<?php echo $class; ?>>
			<?php $class = ''; ?>
			<span class="item-title">
				<a href="<?php echo JRoute::_(JemHelperRoute::getCategoryRoute($child->id, $this->task)); ?>">
					<?php echo $this->escape($child->catname); ?>
				</a>
			</span>
			<?php if ($this->params->get('show_subcat_desc') == 1) : ?>
				<?php if ($child->description) : ?>
				<div class="category-desc">
					<?php echo JHtml::_('content.prepare', $child->description, '', 'com_content.category'); ?>
				</div>
				<?php endif; ?>
				<?php if ( $this->params->get('show_cat_num_articles', 1)) : ?>
				<dl>
					<dt>
						<?php echo JText::_('COM_JEM_EVENTS') . ':' ; ?>
					</dt>
					<dd>
						<?php echo $child->getNumItems(false); /* count direct events only, not recursive */ ?>
					</dd>
				</dl>
				<?php endif; ?>
			<?php elseif ($this->params->get('show_cat_num_articles', 1)) : ?>
				<?php echo ' (' . $child->getNumItems(false) . ')';  /* count direct events only, not recursive */ ?>
				<?php /* experimental * /
				      $count = $child->getNumItemsByState(false);
				      echo ' (' . $count['published'] . ' + ' . $count['unpublished'] . ' / ' . $count['archived'] . ' - ' . $count['trashed'] . ')';
				      /**/
				?>
			<?php endif; ?>

			<?php if (count($child->getChildren()) > 0 ) :
				$this->children[$child->id] = $child->getChildren();
				$this->category = $child;
				$this->maxLevel--;
				if ($this->maxLevel != 0) :
					echo $this->loadTemplate('subcategories');
				endif;
				$this->category = $child->getParent();
				$this->maxLevel++;
			endif; ?>
		</li>
		<?php // endif; ?>
	<?php endforeach; ?>
	</ul>
<?php endif; ?>