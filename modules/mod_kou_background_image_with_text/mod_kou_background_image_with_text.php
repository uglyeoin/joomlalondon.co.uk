<?php
/**
 * @package    mod_kou_background_image_with_text
 *
 * @author     Mary McGinty <info@kindofuseful.com>
 * @copyright  Kind of Useful
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.kindofuseful.com
 */

use Joomla\CMS\Helper\ModuleHelper;

defined('_JEXEC') or die;

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

require ModuleHelper::getLayoutPath('mod_kou_background_image_with_text', $params->get('layout', 'bootstrap2'));
