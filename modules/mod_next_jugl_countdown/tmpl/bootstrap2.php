<?php
/**
 * @package    mod_google_map_wait_to_load
 *
 * @author     Eoin <your@email.com>
 * @copyright  A copyright
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       http://your.url.com
 */

defined('_JEXEC') or die;

// This is the default layout.  It is decided by mod_next_jugl_countdown.php.
// To change a layout, install the module, open it, go to advanced and choose a new layout from the dropdown.

// the name of the module.  In this case mod_start_module.  We use this variable so we can reuse this module in the future and rename it.
$moduleName  = $module->module;
$displayType = $params->get('display_type');
// add your scripts to the start of the array
$scripts     = array( "jquery.plugin", "jquery.countdown", "date-en-GB", "3rdTuesday", "next3rdTuesday", $moduleName );
$cssFiles    = array( "jquery.countdown", $moduleName );
$document    = JFactory::getDocument();
// Ensure jQuery gets loaded before my module.  If needed.  If not, you can safely delete this line.
// JHtml::script('jui/jquery.min.js', false, true);

// Add a stylesheet.
// Do not add unless (a) it exists, (b) it has a file size greater than 0.  This means an empty file will not be loaded.
// The files in this document are just for show, and Won't be loaded unecessarily, which is great, because we can leave them there
// and if we ever need CSS or JS in the future it's really easy for us to remember how to do it.
// These files will be auto incremented with the version number too.  This is a cache buster particularly useful for CDNs.
$mediaUrl = 'media/' . $moduleName;
foreach ($cssFiles as $css)
{
    $cssName = $css;
    if (file_exists($mediaUrl . '/css/' . $cssName . '.css') && filesize($mediaUrl . '/css/' . $cssName . '.css') > 0)
    {
    	$document->addStyleSheet($mediaUrl . '/css/' . $cssName . '.css', array('version' => 'auto'));
    }
}
// Add JS. As above.

foreach ($scripts as $script)
{
    $moduleName = $script;
    if (file_exists($mediaUrl . '/js/' . $moduleName . ".js") && filesize($mediaUrl . '/js/' . $moduleName . ".js") > 0)
    {
        $document->addScript($mediaUrl . '/js/' . $moduleName . ".js", "text/javascript", true, false, array('version' => 'auto'));
    }
}

// Access to module parameters.  don't forget to add more as you add them to your XML file.
//$domain = $params->get('domain');
?>

<!-- Create a dive with a class that is the same as the module name.  That way if we want to change any CSS we can select only this module if we need to.
It also helps us to remember which module on the page is doing what. -->

<?php if ($displayType == '0')
{ ?>
	<div class="<?php echo $moduleName; ?>">
        <h3>We hold #JUGL meetings on the 3rd Tuesday of each month.<br />The next one is scheduled for <strong><a href="/index.php?option=com_jem&amp;view=eventslist&amp;Itemid=296" title="Current Event List"><span id="next3rdTuesday"></span></a> at 7pm</strong>.</h3>
        <div id="nextDateWrapper"></div>
    </div>
<?php } elseif ($displayType == 1) { ?>
    <div id="countDownWrapper"></div>
<?php } ?>
