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

class modSCSocialFindUsHelper
{
    function addPxToString($amount)
    {
        if(strpos($amount, "px")===false)
            $amount .= "px";
        return $amount;
    }
}