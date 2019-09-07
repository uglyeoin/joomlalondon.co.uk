<?php
/**
 * @package         Dummy Content
 * @version         6.0.2PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2019 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

if ( ! is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php'))
{
	return;
}

require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';

class JFormFieldDC_Images extends \RegularLabs\Library\Field
{
	public $type = 'Images';

	protected function getLabel()
	{
		$title       = '<strong>' . $this->get('label') . '</strong><br>'
			. '<small>' . JText::_('DC_EXAMPLE') . '</small>';
		$description = $this->get('description') ?: JText::_('DC_EXAMPLE_IMAGE_DESC');

		return '<label class="hasPopover" title="' . $this->get('label') . '"'
			. ' data-content="' . htmlentities($description) . '">'
			. $title
			. '</label>';
	}

	protected function getInput()
	{
		$this->params = $this->element->attributes();

		$onclick = $this->get('onclick');

		$images = [];

		for ($i = 1; $i <= 2; $i++)
		{
			$url = $this->get('image_' . $i);

			if ( ! $onclick)
			{
				$images[] = '<img src="' . $url . '" width="200" height="100">';
				continue;
			}

			$images[] = '<a href="javascript::// ' . JText::_('DC_RELOAD_IMAGE') . '">'
				. '<img src="' . $url . '" width="200" height="100" onclick="' . $onclick . ';">'
				. '</a>';
		}

		$html = [];

		$html[] = '<fieldset>';
		$html[] = implode(' ', $images);

		if ($onclick)
		{
			$html[] = '<br> [' . JText::_('DC_RELOAD_IMAGES') . ']';
		}

		$html[] = '</fieldset>';

		return implode('', $html);
	}
}
