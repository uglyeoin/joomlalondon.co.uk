<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.joomla-london
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
/**
 * This is a file to add template specific chrome to module rendering.  To use it you would
 * set the style attribute for the given module(s) include in your template to use the style
 * for each given modChrome function.
 *
 * eg.  To render a module mod_test in the submenu style, you would use the following include:
 * <jdoc:include type="module" name="test" style="submenu" />
 *
 * This gives template designers ultimate control over how modules are rendered.
 *
 * NOTICE: All chrome wrapping methods should be named: modChrome_{STYLE} and take the same
 * two arguments.
 */
/*
 * Module chrome for rendering the module in a submenu
 */
function modChrome_bootstrap4($module, &$params, &$attribs)
{
	if ($module->content)
	{
		// Get module params
		// $moduleTag      = $params->get('module_tag', 'div');
        $bootstrapSize = (int) $params->get('bootstrap_size');
		if ($bootstrapSize == '0') {$cols = "";}
		else $cols = "-" . $bootstrapSize;

		// create rows and columns classes
		// $rowClass = "row";
		$colClass = "col";

		// number of columns
		//$column_number = $params->get('column_number', 3);
		// $column_number = 4; // $this->countModules;

		// What type of grid are we using
		// $grid = 12;

		// calculate the grid numbers
		// $col_class_number = floor($grid/$column_number);
		// $columnClass = $colClass.$col_class_number;
		$columnClass = $colClass.$cols;
		// $remainingCol = $grid - ($col_class_number * $column_number);

		// $counter = 1;
		// $total = $params->get('bootstrap_size'); //count($this->countModules);

		if ($module->showtitle)
		{
			echo '<h2>' .$module->title .'</h2>';
		}


	?>


	<?php // if ($counter % $column_number == 1):?>
		<!-- <div class="<?php // echo $rowClass;?>"> -->
	<?php //endif;?>

	<div class="<?php echo $columnClass;?> items">

		<div class="item">


			<?php echo $module->content; ?>

		</div>
	</div>


	<?php // if ($counter % $column_number == 0 || $counter == $total):?>
		<?php // if ($remainingCol > 0):?>
			<!-- <div class="<?php // echo $colClass.$remainingCol;?>"></div> -->
		<?php // endif;?>
		<!-- </div> -->
	<?php //endif;?>
	<?php // $counter++;?>

<?php
	}
}
?>