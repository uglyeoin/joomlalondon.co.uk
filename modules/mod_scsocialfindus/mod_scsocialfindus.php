<?php
/**
 * @package         JFBConnect
 * @copyright (c)   2014-2017 by SourceCoast - All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @version         Release v7.2.0
 * @build-date      2017/03/29
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.file');
if (!JFile::exists(JPATH_ROOT . '/components/com_jfbconnect/libraries/factory.php'))
{
    echo "JFBConnect not found. Please reinstall.";
    return;
}

if (!class_exists('JFBCFactory'))
{
    echo "JFBConnect not enabled. Please enable.";
    return;
}

require_once(dirname(__FILE__) . '/helper.php');
$helper = new modSCSocialFindUsHelper($params);

$fbClient = JFBCFactory::provider('facebook');
$renderKey = $fbClient->getSocialTagRenderKey();
$renderKeyString = " key=".$renderKey;

$document = JFactory::getDocument();
$document->addStyleSheet(JUri::base() . 'modules/mod_scsocialfindus/assets/style.css');

$orientation = $params->get('orientation');
$position = $params->get('position');
$margin = $params->get('margin');
$padding = $params->get('padding');
$backgroundColor = $params->get('background_color');
$floatTop = trim($params->get('float_position_top'));
$floatLeft = trim($params->get('float_position_left'));

//Advanced
$userIntro = $params->get('user_intro');

//Facebook
$facebookLink = $params->get('facebook_url');

//Google
$googleLink = $params->get('google_url');

//Twitter
$twitterLink = $params->get('twitter_url');

//LinkedIn
$linkedinLink = $params->get('linkedin_url');

//Pinterest
$pinterestLink = $params->get('pinterest_url');

//Flickr
$flickrLink = $params->get('flickr_url');

//RSS
$rssLink = $params->get('rss_url');

//YouTube
$youTubeLink = $params->get('youtube_url');

//Instagram
$instagramLink = $params->get('instagram_url');


if($position == 'fixed') //Float
{
    $groupStyles = 'position: ' . $position .";";

    if(intval($floatTop) < 3000)
    {
        $floatTop = $helper->addPxToString($floatTop == '' ? "0" : $floatTop);
        $groupStyles .= 'top:'.$floatTop . ";";
    }
    else // Float to bottom instead of offset
        $groupStyles .= 'bottom:0px;';

    if(intval($floatLeft) < 3000)
    {
        $floatLeft = $helper->addPxToString($floatLeft == '' ? "0" : $floatLeft);
        $groupStyles .= 'left:'.$floatLeft . ";";
    }
    else // Float to right instead of offset
        $groupStyles .= 'right:0px;';

}
else
    $groupStyles = '';

$width = 0;
if(!empty($facebookLink)) $width += 32;
if(!empty($googleLink)) $width += 32;
if(!empty($instagramLink)) $width += 32;
if(!empty($twitterLink)) $width += 32;
if(!empty($linkedinLink)) $width += 32;
if(!empty($pinterestLink)) $width += 32;
if(!empty($flickrLink)) $width += 32;
if(!empty($rssLink)) $width += 32;
if(!empty($youTubeLink)) $width += 32;
$groupStyles .= $orientation == "vertical" ? 'width: 32px;' : "width: {$width}px;";

$groupStyles .= $orientation == "vertical" ? "height: {$width}px;" : "height: 32px;";

if($margin != '')
    $groupStyles .= 'margin:'.$helper->addPxToString($margin).";";
if($padding != '')
    $groupStyles .= 'padding:'.$helper->addPxToString($padding).";";
if($backgroundColor != '')
    $groupStyles .= 'background-color:'.$backgroundColor.';';

require(JModuleHelper::getLayoutPath('mod_scsocialfindus'));
?>
