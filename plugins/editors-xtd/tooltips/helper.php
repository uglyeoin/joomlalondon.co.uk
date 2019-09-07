<?php
/**
 * @package         Tooltips
 * @version         7.4.1PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2019 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Object\CMSObject as JObject;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\RegEx as RL_RegEx;

/**
 ** Plugin that places the button
 */
class PlgButtonTooltipsHelper
	extends \RegularLabs\Library\EditorButtonHelper
{
	/**
	 * Display the button
	 *
	 * @param string $editor_name
	 *
	 * @return JObject|null A button object
	 */
	public function render($editor_name)
	{
		RL_Document::loadEditorButtonDependencies();

		if ($this->params->button_use_simple_button)
		{
			return $this->renderSimpleButton($editor_name);
		}

		return $this->renderPopupButton($editor_name, 780, 800);
	}

	private function renderSimpleButton($editor_name)
	{
		$this->params->tag = RL_RegEx::replace('[^a-z0-9-_]', '', $this->params->tag);

		$text = $this->getExampleText();
		$text = str_replace('\\\\n', '\\n', addslashes($text));
		$text = str_replace('{', '{\'+\'', $text);

		$js = "
			function insertTooltips(editor) {
				selection = RegularLabsScripts.getEditorSelection(editor);
				selection = selection ? selection : '" . JText::_('TT_LINK', true) . "';

				text = '" . $text . "';
				text = text.replace('[:SELECTION:]', selection);

				jInsertEditorText(text, editor);
			}
		";
		RL_Document::scriptDeclaration($js);

		$button = new JObject;

		$button->modal   = false;
		$button->class   = 'btn';
		$button->link    = '#';
		$button->onclick = 'insertTooltips(\'' . $editor_name . '\');return false;';
		$button->text    = $this->getButtonText();
		$button->name    = $this->getIcon();

		return $button;
	}

	private function getExampleText()
	{
		switch (true)
		{
			case ($this->params->button_use_custom_code && $this->params->button_custom_code):
				return $this->getCustomText();
			default:
				return $this->getDefaultText();
		}
	}

	private function getDefaultText()
	{
		return '{' . $this->params->tag . ' ' . JText::_('TT_TITLE') . '::' . JText::_('TT_TEXT') . '}[:SELECTION:]{/' . $this->params->tag . '}';
	}

	private function getCustomText()
	{
		$text = trim($this->params->button_custom_code);
		$text = str_replace(["\r", "\n"], ['', '</p>\n<p>'], trim($text)) . '</p>';
		$text = RL_RegEx::replace('^(.*?)</p>', '\1', $text);
		$text = str_replace(['{tip', '{/tip}'], ['{' . $this->params->tag, '{/' . $this->params->tag . '}'], trim($text));

		return $text;
	}
}
