<?php
/**
 * @package         JFBConnect
 * @copyright (c)   2009-2017 by SourceCoast - All Rights Reserved
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

JFBConnectUtilities::loadLanguage('com_jfbconnect');

$channels = $params->get('channel_id');
if (!is_array($channels))
    $channels = explode(',', $channels);

$options = new JRegistry();
$options->set('show_provider', $params->get('show_provider'));
$options->set('show_date', $params->get('show_date'));
$options->set('show_link', $params->get('show_link'));
$options->set('show_post', $params->get('show_post'));
$options->set('show_comments', $params->get('show_comments'));
$options->set('post_limit', $params->get('post_limit'));
$options->set('datetime_format', $params->get('datetime_format'));
//$options->set('datetime_format', JText::_('DATE_FORMAT_LC2'));
$stream = new JFBConnectStream($options, $channels);

$height = $params->get('height');
if(strpos($height, "px")===false)
    $height .= "px";

$heightStyle = ($height!='px'? ' style="height:'.$height.';overflow:auto;padding:2px;"':'');

require(JModuleHelper::getLayoutPath('mod_scsocialstream'));

?>
