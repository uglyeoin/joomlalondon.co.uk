<?php

/**
 * @package     Extly.Components
 * @subpackage  plgButtonJoocialEditor - Plugin Joocial Editor Button
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2018 Extly, CB. All rights reserved.
 * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

defined('_JEXEC') or die;

/**
 * Editor JoocialEditor buton
 *
 * @package     Extly.Components
 * @subpackage  com_autotweet
 * @since       1.0
 */
class PlgButtonJoocialEditor extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An array that holds the plugin configuration
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		// Com_jreviews,
		$integrated_components = $this->params->get('integrated_components', 'com_autotweet,com_content,com_easyblog,com_flexicontent,com_jcalpro,com_k2,com_zoo');
		$this->integrated_components = explode(',', $integrated_components);

		$this->loadLanguage();

		// Load component language file for use with plugin
		$jlang = JFactory::getLanguage();
		$jlang->load('com_autotweet');

		include_once JPATH_ADMINISTRATOR . '/components/com_autotweet/helpers/autotweetbase.php';
	}

	/**
	 * Display the button
	 *
	 * @param   string  $name  Param
	 *
	 * @return array A four element array of (article_id, article_title, category_id, object)
	 */
	public function onDisplay($name)
	{
		$jinput = JFactory::getApplication()->input;
		$comp = $jinput->get('option');

		if (!in_array($comp, $this->integrated_components))
		{
			return false;
		}

		$doc = JFactory::getDocument();
		$icon = JUri::root() . '/media/com_autotweet/images/joocialeditorbutton.png';

		if (EXTLY_J35)
		{
			$css = '.icon-joocial-editor { background: url("' . $icon . '") no-repeat scroll 2px 50% transparent!important;}';
		}
		else
		{
			$css = '.icon-joocial-editor { background: url("' . $icon . '") no-repeat scroll 100% 0 transparent;}';
		}

		$doc->addStyleDeclaration($css);

		$link = AutoTweetDefaultView::addItemeditorHelperApp();

		$button = new JObject;
		$button->modal = true;
		$button->class = 'btn';
		$button->link = $link;
		$button->text = JText::_('PLG_JOOCIALEDITOR_BUTTON');
		$button->name = 'joocial-editor';
		$button->options = "{handler: 'iframe', size: {x: 800, y: 500}}";

		return $button;
	}
}
