<?php
/**
 * @package    mod_koy_copyright
 *
 * @author     Eoin <your@email.com>
 * @copyright  A copyright
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       http://your.url.com
 */

    defined('_JEXEC') or die;

    // Access to module parameters
    $app = JFactory::getApplication();
    $schemaURL = "http://schema.org/LocalBusiness";
    if(!empty($params['schemaURL'])) {
	    $schemaURL = $params['schemaURL'];
    }
    $sitename = $app->get('sitename');
    if (!empty($params['siteName'])) {
        $sitename = "<span itemprop=\"name\">" . $params['siteName'] . "</span>";
    }
    $startDate = "";
    if (!empty($params['startDate'])) {
        $startDate = ((int)$params['startDate']) . " - ";
    }
    $date = (int)date("Y");
    if ($startDate >= $date) {$startDate = "";}

    $moduleName = $module->module;
?>
<div class="<?php echo $moduleName; ?>">
    <span itemscope itemtype="<?php echo $params['schemaURL']; ?>" class="<?php echo $module->module; ?>">&copy; <?php echo $sitename . " " . $startDate . " " . $date; ?> </span>
</div>
