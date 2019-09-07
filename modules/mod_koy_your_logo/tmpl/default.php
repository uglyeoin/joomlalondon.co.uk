<?php
/**
 * @package    mod_koy_your_logo
 *
 * @author     Eoin <your@email.com>
 * @copyright  A copyright
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       http://your.url.com
 */

defined('_JEXEC') or die;

// Add a stylesheet
$mediaUrl = 'media/' . $module->module;
$cssName = 'general.css';
if (file_exists($mediaUrl . '/css/' . $cssName) && filesize($mediaUrl . '/css/' . $cssName) > 0)
{
	$document = JFactory::getDocument();
	$document->addStyleSheetVersion($mediaUrl . '/css/' . $cssName, array('version' => 'auto'));
}

// If empty set to base url
$baseURL = JUri::base(true);
if (!empty($params['url'])) { $url = $params['url']; }
else ($url = $baseURL);

// Get Logo
// Set output as a number
$logoImageText = (int)$params['logoImageText'];
// Pick whether to use media manager or an external url (or an SVG)
$logoLocal = $params['logoLocal'];

if ($logoImageText != '1') {
    $logo = $params['logoExternal'];
	if ($logoLocal == '0') { $logo = JUri::base(true) . $params['logoMediaManager'];}
	}


// If text is not empty then set the text.
if (($logoImageText == '1' || $logoImageText == '2') && (!empty($params['text']))) { $logoText = $params['text']; }


// Set different URL for text (as opposed to the image)
// Set output as number
$differentUrlForText = intval($params['differentUrlForText']);
// check if empty and if not use the URL or else use the site domain
if ($differentUrlForText == '0') { $textUrl = $params['url'];}
    elseif ($differentUrlForText == '1' && !empty($params['textUrl'])) {$textUrl = $params['textUrl'];}
    else $textUrl = $baseURL;

?>

<div itemscope itemtype="http://schema.org/Organization" class="mod_koy_your_logo">
        <?php if (($logoImageText != '1')) { ?>
            <a href="<?php echo $url; ?>" class="mod_koy_your_logo--image-url" itemprop="url"<?php if ($params['openNewWindow'] == "_blank") { echo ' target="_blank" rel="noreferrer noopener"';} ?>>
                <img src="<?php echo $logo; ?>" alt="<?php echo $params['logoAltText']; ?>" itemprop="logo" class="mod_koy_your_logo--logo">
            </a>
        <?php } ?>
        <?php if ($logoImageText != '0' && !empty($params['text'])) { ?>
            <a href="<?php echo $textUrl; ?>" class="mod_koy_your_logo--text-url" itemprop="url"<?php if ($params['openNewWindow'] == "_blank") { echo ' target="_blank" rel="noreferrer noopener"';} ?>>
                <?php echo $logoText; ?>
            </a>
        <?php } ?>
</div>