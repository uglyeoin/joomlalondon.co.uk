<?php
/**
 * @package         Better Preview
 * @version         6.2.2PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2019 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\BetterPreview;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;

class PreLoader
{
	public static function _()
	{
		$fid = JFactory::getApplication()->input->get('fid');

		$template = file_get_contents(__DIR__ . '/Layout/PreLoader.html');
		$template = str_replace(
			[
				'{loading}',
				'parent.fid',
			],
			[
				JText::_('BP_LOADING'),
				'parent.' . $fid,
			],
			$template
		);

		echo $template;

		die;
	}
}
