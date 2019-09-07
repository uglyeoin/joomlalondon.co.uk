<?php

/**
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

defined('_JEXEC') or die;

/**
 * AutotweetViewMlogin
 *
 * @package     Extly.Components
 * @subpackage  com_Autotweet
 * @since       1.0
 */
class AutotweetViewMlogin extends F0FViewForm
{
	/**
	 * Runs before rendering the view template, echoing HTML to put before the
	 * view template's generated HTML
	 *
	 * @return void
	 */
	protected function preRender()
	{
		$this->input->set('render_toolbar', false);

		return parent::preRender();
	}

	/**
	 * Default task. Assigns a model to the view and asks the view to render
	 * itself.
	 *
	 * YOU MUST NOT USETHIS TASK DIRECTLY IN A URL. It is supposed to be
	 * used ONLY inside your code. In the URL, use task=browse instead.
	 *
	 * @param   bool    $cachable   Is this view cacheable?
	 * @param   bool    $urlparams  Add your safe URL parameters (see further down in the code)
	 * @param   string  $tpl        The name of the template file to parse
	 *
	 * @return  bool
	 */
	public function display($cachable = false, $urlparams = false, $tpl = null)
	{
		// Get the view data.
		$this->user = JFactory::getUser();

		require_once JPATH_ADMINISTRATOR . '/components/com_users/helpers/users.php';
		$tfa = UsersHelper::getTwoFactorMethods();
		$this->tfa = is_array($tfa) && count($tfa) > 1;

		$this->params = JFactory::getApplication()->getPageParameters($this->option);
		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

		$document = JFactory::getDocument();
		$document->addScriptDeclaration('jQuery(document).ready(function(){
	jQuery("#username").attr("autocorrect", "off").attr("autocapitalize", "none");
});');

		// No cache
		parent::display(false, $urlparams, $tpl);
	}
}
