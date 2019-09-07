<?php
/**
 * @package         Better Trash
 * @version         1.3.0PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2019 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\BetterTrash;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Layout\FileLayout as JLayoutFile;
use RegularLabs\Library\RegEx as RL_RegEx;

/**
 * Plugin that replaces stuff
 */
class Buttons
{
	private $params;
	private $data;

	private $action = '';

	private $regex_trash;
	private $regex_delete;
	private $regex_checkall;

	private $button_trash;
	private $button_empty;
	private $button_exit;

	function __construct($params = null)
	{
		$this->params = $params ?: Params::get();
		$this->data   = (new Data)->get();

		if ( ! empty($this->data))
		{
			$this->regex_trash    = $this->data->action_trash
				? '(<div [^>]*id="toolbar-trash"[^>]*>).*?(Joomla\.submitbutton\(\'(?:[a-z0-9-_]+\.)?' . $this->data->action_trash . '\'\);?).*?(</div>)'
				: '';
			$this->regex_delete   = $this->data->action_delete
				? '(<div [^>]*id="toolbar-delete"[^>]*>).*?(Joomla\.submitbutton\(\'(?:[a-z0-9-_]+\.)?' . $this->data->action_delete . '\'\);?).*?(</div>)'
				: '';
			$this->regex_checkall = 'name="((?:checkall-)?toggle)".*?name="cid\[\]"';
		}
	}

	public function change($html = '')
	{
		if (empty($this->data))
		{
			return;
		}

		$html = $html ?: JFactory::getApplication()->getBody();

		if ( ! $this->isListView($html))
		{
			return;
		}

		$this->changeButtonsListView($html);
		$this->changeButtonsTrashView($html);

		JFactory::getApplication()->setBody($html);
	}

	private function changeButtonsListView(&$string)
	{
		if ( ! $this->isListViewMain($string))
		{
			return;
		}

		$this->replaceTrashButton($string);
	}

	private function changeButtonsTrashView(&$string)
	{
		if ( ! $this->isListViewTrash($string))
		{
			return;
		}

		$this->replaceEmptyTrashButton($string);
		$this->addExitTrashButton($string);
		$this->addAlert($string);
	}

	private function replaceTrashButton(&$string)
	{
		if ( ! RL_RegEx::match($this->regex_trash, $string, $match))
		{
			return;
		}

		$this->action = $match[2];

		$button = $this->getTrashButton();

		$string = str_replace($match[0], $match[1] . $button . $match[3], $string);
	}

	private function replaceEmptyTrashButton(&$string)
	{
		if ( ! RL_RegEx::match($this->regex_delete, $string, $match))
		{
			return;
		}

		$button = $this->getNewEmptyTrashButton($string);

		if ( ! $button)
		{
			return;
		}

		$string = str_replace($match[0], $match[1] . $button . $match[3], $string);
	}

	private function addExitTrashButton(&$string)
	{
		if ( ! $this->params->show_exit_trash)
		{
			return;
		}

		$regex = '<div [^>]*id="toolbar-delete"[^>]*>.*?</div>';

		if ( ! RL_RegEx::match($regex, $string, $match))
		{
			return;
		}

		$button = $this->getExitTrashButton();

		$button = '<div class="btn-wrapper" id="toolbar-exit-trash">'
			. $button
			. '</div>';

		$string = str_replace($match[0], $match[0] . $button, $string);
	}

	private function addAlert(&$string)
	{
		if ( ! $this->params->show_trash_alert)
		{
			return;
		}

		$has_items = RL_RegEx::match($this->regex_checkall, $string);

		$button_trash = $has_items ? $this->getNewEmptyTrashButton($string) : '';
		$button_exit  = $this->getExitTrashButton();

		$regex = strpos($string, '<div id="j-main-container"') !== false
			? '<div id="j-main-container".*?>'
			: '<section id="content">.*?<div class="span1(?:0|2)">';

		$string = RL_RegEx::replace(
			$regex,
			'\0<div class="alert alert-error">'
			. '<h4 class="alert-heading"><span class="icon-trash"></span> ' . JText::_('BT_TRASHED_ITEMS') . '</h4>'
			. '<p>' . JText::_('BT_TRASHED_ITEMS_DESC') . '</p>'
			. $button_trash
			. $button_exit
			. '</div>',
			$string
		);
	}

	private function getTrashButton()
	{
		if ( ! is_null($this->button_trash))
		{
			return $this->button_trash;
		}

		$task = 'if (document.adminForm.boxchecked.value == 0) {'
			. 'document.adminForm.' . $this->data->filter_prefix . $this->data->filter . '.value = \'' . $this->data->filter_trashed . '\';'
			. 'document.adminForm.submit();'
			. '} else {'
			. $this->action
			. '}'
			. 'return false;';

		$options = [
			'text'     => JText::_('JTOOLBAR_TRASH'),
			'class'    => 'icon-trash',
			'btnClass' => 'btn btn-small',
			'doTask'   => $task,
		];

		$this->button_trash = $this->getButton($options);

		return $this->button_trash;
	}

	private function getExitTrashButton()
	{
		if ( ! is_null($this->button_exit))
		{
			return $this->button_exit;
		}

		$this->button_exit = '';

		if ( ! $this->params->show_exit_trash)
		{
			return '';
		}

		$options = [
			'text'     => JText::_('BT_EXIT_TRASH'),
			'class'    => 'icon-back',
			'btnClass' => 'btn btn-small',
			'doTask'   => 'document.adminForm.' . $this->data->filter_prefix . $this->data->filter . '.selectedIndex = 0;'
				. 'document.adminForm.submit();'
				. 'return false;',
		];

		$this->button_exit = $this->getButton($options);

		return $this->button_exit;
	}

	private function getNewEmptyTrashButton($string)
	{
		if ( ! is_null($this->button_empty))
		{
			return $this->button_empty;
		}

		$this->button_empty = '';

		if ( ! RL_RegEx::match($this->regex_delete, $string, $match))
		{
			return '';
		}

		$this->action = $match[2];

		RL_RegEx::match($this->regex_checkall, $string, $select_all);
		$select_all = isset($select_all[1]) ? $select_all[1] : '';

		$options = $this->getNewEmptyTrashButtonOptions($select_all);

		$this->button_empty = $this->getButton($options);

		return $this->button_empty;
	}

	private function getButton($options)
	{
		$layout = new JLayoutFile('joomla.toolbar.standard');

		return $layout->render($options);
	}

	private function getNewEmptyTrashButtonOptions($select_all = '')
	{
		$options = [
			'text'     => JText::_('JTOOLBAR_EMPTY_TRASH'),
			'class'    => 'icon-delete',
			'btnClass' => 'btn btn-small' . ($select_all ? ' btn-danger' : ''),
			'doTask'   => '',
		];

		if ( ! $select_all)
		{
			$options['btnClass'] .= ' disabled';

			return $options;
		}

		$select_all = 'document.adminForm.elements[\'' . $select_all . '\']';

		$confirm = JText::_('JGLOBAL_CONFIRM_DELETE', true);

		$sub_task = $select_all . '.checked=1;Joomla.checkAll(' . $select_all . ');'
			. 'setTimeout(function() { if(confirm(\'' . $confirm . '\')){'
			. $this->action
			. '} else {'
			. $select_all . '.checked=0;Joomla.checkAll(' . $select_all . ');'
			. '}}, 10);';

		$task = 'if (document.adminForm.boxchecked.value == 0) {'
			. $sub_task
			. '} else {'
			. 'if (confirm(\'' . $confirm . '\')) { ' . $this->action . ' };'
			. '}'
			. 'return false;';

		$options['doTask'] = $task;

		return $options;
	}

	private function isListView($string)
	{
		if (empty($this->data))
		{
			return false;
		}

		return (
			strpos($string, 'JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST') !== false
			&& strpos($string, 'name="adminForm"') !== false
			&& strpos($string, 'id="' . $this->data->filter_prefix . $this->data->filter . '"') !== false
		);
	}

	private function isListViewMain($string)
	{
		if ( ! RL_RegEx::match($this->regex_trash, $string))
		{
			return false;
		}

		return true;
	}

	private function isListViewTrash($string)
	{
		if ( ! RL_RegEx::match($this->regex_delete, $string))
		{
			return false;
		}

		return true;
	}
}
