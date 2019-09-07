<?php
/**
 * @version 2.2.3
 * @package JEM
 * @copyright (C) 2013-2017 joomlaeventmanager.net
 * @copyright (C) 2005-2009 Christoph Lukes
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
defined('_JEXEC') or die();

/**
 * Editevent-View
 */
class JemViewEditevent extends JViewLegacy
{
	protected $form;
	protected $item;
	protected $return_page;
	protected $state;

	/**
	 * Editevent-View
	 */
	public function display($tpl = null)
	{
		if ($this->getLayout() == 'choosevenue') {
			$this->_displaychoosevenue($tpl);
			return;
		}

		if ($this->getLayout() == 'choosecontact') {
			$this->_displaychoosecontact($tpl);
			return;
		}

		if ($this->getLayout() == 'chooseusers') {
			$this->_displaychooseusers($tpl);
			return;
		}

		// Initialise variables.
		$jemsettings = JemHelper::config();
		$settings    = JemHelper::globalattribs();
		$app         = JFactory::getApplication();
		$user        = JemFactory::getUser();
		$userId      = $user->get('id');
		$document    = JFactory::getDocument();
		$model       = $this->getModel();
		$menu        = $app->getMenu();
		$menuitem    = $menu->getActive();
		$pathway     = $app->getPathway();
		$url         = JUri::root();

		// Get model data.
		$this->state = $this->get('State');
		$this->item = $this->get('Item');
		$this->params = $this->state->get('params');

		// Create a shortcut for $item and params.
		$item = $this->item;
		$params = $this->params;

		$this->form = $this->get('Form');
		$this->return_page = $this->get('ReturnPage');
		$this->invited = (array)$this->get('InvitedUsers');

		// check for data error
		if (empty($item)) {
			$app->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
			return false;
		}

		// check for guest
		if ($userId == 0) {
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			return false;
		}

		if (empty($item->id)) {
			$authorised = (bool)$user->can('add', 'event');
		} else {
			$authorised = (bool)$item->params->get('access-edit');
		}

		$access = isset($item->access) ? $item->access : 0;
		$authorised = $authorised && in_array($access, $user->getAuthorisedViewLevels());

		if ($authorised !== true) {
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			return false;
		}

		// Decide which parameters should take priority
		$useMenuItemParams = ($menuitem && ($menuitem->query['option'] == 'com_jem')
		                                && ($menuitem->query['view']   == 'editevent')
		                                && (0 == $item->id)); // menu item is always for new event

		$title = ($item->id == 0) ? JText::_('COM_JEM_EDITEVENT_ADD_EVENT')
		                          : JText::sprintf('COM_JEM_EDITEVENT_EDIT_EVENT', $item->title);

		if ($useMenuItemParams) {
			$pagetitle = $menuitem->title ? $menuitem->title : $title;
			$params->def('page_title', $pagetitle);
			$params->def('page_heading', $pagetitle);
			$pathway->setItemName(1, $pagetitle);

			// Load layout from menu item if one is set else from event if there is one set
			if (isset($menuitem->query['layout'])) {
				$this->setLayout($menuitem->query['layout']);
			} elseif ($layout = $item->params->get('event_layout')) {
				$this->setLayout($layout);
			}

			$item->params->merge($params);
		} else {
			$pagetitle = $title;
			$params->set('page_title', $pagetitle);
			$params->set('page_heading', $pagetitle);
			$params->set('show_page_heading', 1); // ensure page heading is shown
			$params->set('introtext', ''); // there is definitely no introtext.
			$params->set('showintrotext', 0);
			$pathway->addItem($pagetitle, ''); // link not required here so '' is ok

			// Check for alternative layouts (since we are not in an edit-event menu item)
			// Load layout from event if one is set
			if ($layout = $item->params->get('event_layout')) {
				$this->setLayout($layout);
			}

			$temp = clone($params);
			$temp->merge($item->params);
			$item->params = $temp;
		}

		if (!empty($this->item) && isset($this->item->id)) {
			// $this->item->images = json_decode($this->item->images);
			// $this->item->urls = json_decode($this->item->urls);

			$tmp = new stdClass();

			// check for recurrence
			if (($this->item->recurrence_type != 0) || ($this->item->recurrence_first_id != 0)) {
				$tmp->recurrence_type = 0;
				$tmp->recurrence_first_id = 0;
			}

			// $tmp->images = $this->item->images;
			// $tmp->urls = $this->item->urls;
			$this->form->bind($tmp);
		}

		if (empty($item->id)) {
			if (!empty($item->catid)) {
				$this->form->setFieldAttribute('cats', 'prefer', $item->catid);
			}
			if (!empty($item->locid)) {
				$tmp = new stdClass();
				$tmp->locid = $item->locid;
				$this->form->bind($tmp);
			}
		}

		// Check for errors.
		$errors = $this->get('Errors');
		if (is_array($errors) && count($errors)) {
			JError::raiseWarning(500, implode("\n", $errors));
			return false;
		}

		$access2      = JemHelper::getAccesslevelOptions(true, $access);
		$this->access = $access2;

		JHtml::_('behavior.formvalidation');
		JHtml::_('behavior.tooltip');
		JHtml::_('behavior.modal', 'a.flyermodal');

		// Load css
		JemHelper::loadCss('jem');
		JemHelper::loadCustomCss();

		// Load scripts
		JHtml::_('script', 'com_jem/attachments.js', false, true);
		JHtml::_('script', 'com_jem/recurrence.js', false, true);
		JHtml::_('script', 'com_jem/seo.js', false, true);
		JHtml::_('script', 'com_jem/unlimited.js', false, true);
		JHtml::_('script', 'com_jem/other.js', false, true);

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($item->params->get('pageclass_sfx'));
		$this->dimage        = JemImage::flyercreator($this->item->datimage, 'event');
		$this->jemsettings   = $jemsettings;
		$this->settings      = $settings;
		$this->infoimage     = JHtml::_('image', 'com_jem/icon-16-hint.png', JText::_('COM_JEM_NOTES'), NULL, true);

		$this->user = $user;
		$permissions = new stdClass();
		$permissions->canAddVenue = $user->can('add', 'venue');
		$this->permissions = $permissions;

		if ($params->get('enable_category') == 1) {
			$this->form->setFieldAttribute('catid', 'default', $params->get('catid', 1));
			$this->form->setFieldAttribute('catid', 'readonly', 'true');
		}

		// disable for non-publishers
		if (empty($item->params) || !$item->params->get('access-change', false)) {
			$this->form->setFieldAttribute('published', 'default', 0);
			$this->form->setFieldAttribute('published', 'readonly', 'true');
		}

		// configure image field: show max. file size, and possibly mark field as required
		$tip = JText::_('COM_JEM_UPLOAD_IMAGE');
		if ((int)$jemsettings->sizelimit > 0) {
			$tip .= ' <br/>' . JText::sprintf('COM_JEM_MAX_FILE_SIZE_1', (int)$jemsettings->sizelimit);
		}
		$this->form->setFieldAttribute('userfile', 'description', $tip);
		if ($jemsettings->imageenabled == 2) {
			$this->form->setFieldAttribute('userfile', 'required', 'true');
		}

		// configure invited field
		if ($jemsettings->regallowinvitation == 1) {
			$this->form->setValue('invited', null, implode(',', $this->invited));
			$this->form->setFieldAttribute('invited', 'eventid', (int)$this->item->id);
		}

		$this->_prepareDocument();
		parent::display($tpl);
	}


	/**
	 * Prepares the document
	 */
	protected function _prepareDocument()
	{
		$app = JFactory::getApplication();

		$title = $this->params->get('page_title');
		if ($app->getCfg('sitename_pagetitles', 0) == 1) {
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}
		$this->document->setTitle($title);

		// TODO: Is it useful to have meta data in an edit view?
		//       Also shouldn't be "robots" set to "noindex, nofollow"?
		if ($this->params->get('menu-meta_description')) {
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords')) {
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots')) {
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}


	/**
	 * Creates the output for the venue select listing
	 */
	protected function _displaychoosevenue($tpl)
	{
		$app         = JFactory::getApplication();
		$jinput      = JFactory::getApplication()->input;
		$jemsettings = JemHelper::config();
	//	$db          = JFactory::getDBO();
		$document    = JFactory::getDocument();

		$filter_order     = $app->getUserStateFromRequest('com_jem.selectvenue.filter_order', 'filter_order', 'l.venue', 'cmd');
		$filter_order_Dir = $app->getUserStateFromRequest('com_jem.selectvenue.filter_order_Dir', 'filter_order_Dir', 'ASC', 'word');
		$filter_type      = $app->getUserStateFromRequest('com_jem.selectvenue.filter_type', 'filter_type', 0, 'int');
		$filter_state     = $app->getUserStateFromRequest('com_jem.selectvenue.filter_state', 'filter_state', '*', 'word');
		$search           = $app->getUserStateFromRequest('com_jem.selectvenue.filter_search', 'filter_search', '', 'string');
		$limitstart       = $jinput->get('limitstart', '0', 'int');
		$limit            = $app->getUserStateFromRequest('com_jem.selectvenue.limit', 'limit', $jemsettings->display_num, 'int');

		// Get/Create the model
		$rows       = $this->get('Venues');
		$pagination = $this->get('VenuesPagination');

		JHtml::_('behavior.modal', 'a.flyermodal');

		// filter state
		$lists['state'] = JHtml::_('grid.state', $filter_state);

		// table ordering
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order']     = $filter_order;

		$document->setTitle(JText::_('COM_JEM_SELECT_VENUE'));
		JemHelper::loadCss('jem');

		$filters = array();
		$filters[] = JHtml::_('select.option', '1', JText::_('COM_JEM_VENUE'));
		$filters[] = JHtml::_('select.option', '2', JText::_('COM_JEM_CITY'));
		$filters[] = JHtml::_('select.option', '3', JText::_('COM_JEM_STATE'));
		$searchfilter = JHtml::_('select.genericlist', $filters, 'filter_type', array('size'=>'1','class'=>'inputbox'), 'value', 'text', $filter_type);

		$this->rows         = $rows;
		$this->searchfilter = $searchfilter;
		$this->pagination   = $pagination;
		$this->lists        = $lists;
		$this->filter       = $search;

		parent::display($tpl);
	}


	/**
	 * Creates the output for the contact select listing
	 */
	protected function _displaychoosecontact($tpl)
	{
		$app         = JFactory::getApplication();
		$jinput      = JFactory::getApplication()->input;
		$jemsettings = JemHelper::config();
	//	$db          = JFactory::getDBO();
		$document    = JFactory::getDocument();

		$filter_order     = $app->getUserStateFromRequest('com_jem.selectcontact.filter_order', 'filter_order', 'con.name', 'cmd');
		$filter_order_Dir = $app->getUserStateFromRequest('com_jem.selectcontact.filter_order_Dir', 'filter_order_Dir', '', 'word');
		$filter_type      = $app->getUserStateFromRequest('com_jem.selectcontact.filter_type', 'filter_type', 0, 'int');
		$search           = $app->getUserStateFromRequest('com_jem.selectcontact.filter_search', 'filter_search', '', 'string');
		$limitstart       = $jinput->get('limitstart', '0', 'int');
		$limit            = $app->getUserStateFromRequest('com_jem.selectcontact.limit', 'limit', $jemsettings->display_num, 'int');

		JHtml::_('behavior.tooltip');
		JHtml::_('behavior.modal', 'a.flyermodal');

		// Load css
		JemHelper::loadCss('jem');

		$document->setTitle(JText::_('COM_JEM_SELECT_CONTACT'));

		// Get/Create the model
		$rows       = $this->get('Contacts');
		$pagination = $this->get('ContactsPagination');

		// table ordering
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order']     = $filter_order;

		//Build search filter
		$filters = array();
		$filters[] = JHtml::_('select.option', '1', JText::_('COM_JEM_NAME'));
	/*	$filters[] = JHtml::_('select.option', '2', JText::_('COM_JEM_ADDRESS')); */ // data security
		$filters[] = JHtml::_('select.option', '3', JText::_('COM_JEM_CITY'));
		$filters[] = JHtml::_('select.option', '4', JText::_('COM_JEM_STATE'));
		$searchfilter = JHtml::_('select.genericlist', $filters, 'filter_type', array('size'=>'1','class'=>'inputbox'), 'value', 'text', $filter_type);

		// search filter
		$lists['search']= $search;

		//assign data to template
		$this->searchfilter = $searchfilter;
		$this->lists        = $lists;
		$this->rows         = $rows;
		$this->pagination   = $pagination;

		parent::display($tpl);
	}


	/**
	 * Creates the output for the users select listing
	 */
	protected function _displaychooseusers($tpl)
	{
		$app         = JFactory::getApplication();
		$jinput      = $app->input;
		$jemsettings = JemHelper::config();
	//	$db          = JFactory::getDBO();
		$document    = JFactory::getDocument();
		$model       = $this->getModel();

		// no filters, hard-coded
		$filter_order     = 'usr.name';
		$filter_order_Dir = '';
		$filter_type      = '';
		$search           = '';
		$limitstart       = 0;
		$limit            = 0;
		$eventId          = $jinput->getInt('a_id', 0);

		JHtml::_('behavior.tooltip');
		JHtml::_('behavior.modal', 'a.flyermodal');

		// Load css
		JemHelper::loadCss('jem');

		$document->setTitle(JText::_('COM_JEM_SELECT_USERS_TO_INVITE'));

		// Get/Create the model
		$model->setState('event.id', $eventId);
		$rows       = $this->get('Users');
		$pagination = $this->get('UsersPagination');

		// table ordering
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order']     = $filter_order;

		//Build search filter - unused
		$filters = array();
		$filters[] = JHtml::_('select.option', '1', JText::_('COM_JEM_NAME'));
		$searchfilter = JHtml::_('select.genericlist', $filters, 'filter_type', array('size'=>'1','class'=>'inputbox'), 'value', 'text', $filter_type);

		// search filter - unused
		$lists['search']= $search;

		//assign data to template
		$this->searchfilter = $searchfilter;
		$this->lists        = $lists;
		$this->rows         = $rows;
		$this->pagination   = $pagination;

		parent::display($tpl);
	}

}
?>