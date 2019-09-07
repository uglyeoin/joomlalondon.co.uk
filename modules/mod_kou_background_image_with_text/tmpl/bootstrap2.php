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

$moduleName = $module->module;

$mediaUrl = 'media/' . $moduleName;
if (file_exists($mediaUrl . '/css/' . $moduleName . '.css') && filesize($mediaUrl . '/css/' . $moduleName . '.css') > 0)
{
	$document = JFactory::getDocument();
    $document->addStyleSheet($mediaUrl . '/css/' . $moduleName . '.css', array('version' => 'auto'));
}

// Add JS.  As above.
if (file_exists($mediaUrl . '/js/' . $moduleName . ".js") && filesize($mediaUrl . '/js/' . $moduleName . ".js") > 0)
{
	$document = JFactory::getDocument();
	$document->addScript($mediaUrl . '/js/' . $moduleName . ".js", "text/javascript", true, false, array('version' => 'auto'));
}


// Access to module parameters.  don't forget to add more as you add them to your XML file.
$defaultImage          = $params->get('default-image');
$defaultUrl            = $params->get('default-url');
$altText               = $params->get('alt-text');

$images                = $params->get('images');
$texts                 = $params->get('texts');

?>

<div class="g-wrapper">
    <div class="<?php echo $moduleName ?> g-container">

        <?php 
            $i = 0;

            echo '<img srcset="';
            foreach ($images as $image) {
                echo $image->mediaManagerImagesSubform->image . " " . $image->mediaManagerImagesSubform->realSize . "w";
                echo ', ';
            }
            echo '" size="';
            foreach ($images as $image) {
                echo '(max-width: ';
                echo $image->mediaManagerImagesSubform->size . 'px) ';
                if (!empty($image->mediaManagerImagesSubform->slot)) {echo $image->mediaManagerImagesSubform->slot . "px";} else {echo "100vw";}
                echo ', ';
            }
            echo '" src="' . $defaultImage . '" alt="'. $altText . '">';
        ?>

        <div class="text-holder">
        <?php 
            foreach ($texts as $text) {
            echo "<" . $text->HTMLElement . ">" .
                $text->text . "</" . $text->HTMLElement . ">";
            }; 
        ?>
        </div>
    </div>
</div>