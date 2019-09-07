<?php
/**
 * @copyright	@copyright	Copyright (c) 2014 jbounce. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// include the syndicate functions only once
require_once dirname(__FILE__) . '/helper.php';

$class_sfx = htmlspecialchars($params->get('class_sfx'));
$aggressivemode = $params->get('aggressivemode', 'true');
$animin = $params->get('animin','bounceInUp');
$animout = $params->get('animout','bounceOutDown');
$jbimage = $params->get('jbimage');
$animinduration = $params->get('animinduration','0.5');
$animoutduration = $params->get('animoutduration','1');
$animoutdelay = $animoutduration * 1000 + 100;
$jbtitle = $params->get('jbtitle');
$jbsubtitle = $params->get('jbsubtitle');
$modalbgcolor = $params->get('modalbgcolor','#FFFFFF');
$modaltxtcolor = $params->get('modaltxtcolor','#333333');
$contentsource = $params->get('contentsource',0);
$contenthtml = $params->get('contenthtml',0);

require(JModuleHelper::getLayoutPath('mod_jbounce', $params->get('layout', 'default')));