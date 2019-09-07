<?php

/**
 * @package     Extly.Plugins
 * @subpackage  autotweetjem - Plugin AutoTweetNG Jem-Extension
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2018 Extly, CB. All rights reserved.
 * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

defined('_JEXEC') or die;

// Check for component
if (!JComponentHelper::getComponent('com_autotweet', true)->enabled)
{
	JError::raiseWarning('5', 'AutoTweet NG Jem-Plugin - AutoTweet NG Component is not installed or not enabled.');

	return;
}

include_once JPATH_ADMINISTRATOR . '/components/com_autotweet/helpers/autotweetbase.php';
include_once JPATH_SITE . '/components/com_jem/helpers/route.php';
include_once JPATH_SITE . '/components/com_jem/helpers/helper.php';
include_once JPATH_SITE . '/components/com_jem/factory.php';

/**
 * PlgContentAutotweetJem class.
 *
 * @package     Extly.Plugins
 * @subpackage  com_autotweet
 * @since       1.0
 */
class PlgContentAutotweetJem extends PlgAutotweetBase
{
	const TYPE_EVENT = 1;

	protected $on_new_event = 0;

	protected $on_update_event = 0;

	protected $post_changestatepublished = 0;

	protected $template_event = '';

	protected $categories;

	protected $excluded_categories;

	protected $show_category;

	protected $show_hash;

	protected $use_text;

	protected $use_text_count;

	protected $static_text;

	protected $static_text_pos;

	protected $static_text_source;

	protected $metakey_count;

	protected $date_format;

	/**
	 * PlgSystemAutotweetJem.
	 *
	 * @param   string  $subject  Params
	 * @param   array   $params   Params
	 */
	public function __construct($subject, $params)
	{
		parent::__construct($subject, $params);

		$pluginParams = $this->pluginParams;

		// Joomla event specific params

		$this->on_new_event = $pluginParams->get('on_new_event', 0);
		$this->on_update_event = $pluginParams->get('on_update_event', 0);
		$this->post_changestatepublished = (int) $pluginParams->get('post_changestatepublished', 1);
		$this->template_event = $pluginParams->get('template_event', 'New event: [event]!');

		$this->categories = $pluginParams->get('categories', null);
		$this->excluded_categories = $pluginParams->get('excluded_categories', null);
		$this->show_category = (int) $pluginParams->get('show_category', 0);
		$this->show_hash = (int) $pluginParams->get('show_hash', 0);
		$this->use_text = (int) $pluginParams->get('use_text', 0);
		$this->use_text_count = $pluginParams->get('use_text_count75', SharingHelper::MAX_CHARS_TITLE);
		$this->static_text = strip_tags($pluginParams->get('static_text', ''));
		$this->static_text_pos = (int) $pluginParams->get('static_text_pos', 1);
		$this->static_text_source = (int) $pluginParams->get('static_text_source', 0);
		$this->metakey_count = (int) $pluginParams->get('metakey_count', 1);

		$this->date_format = $pluginParams->get('date_format', 'Y-m-d H:i');
	}

	/**
	 * onContentAfterSave
	 *
	 * @param   object  $context  The context of the content passed to the plugin.
	 * @param   object  $event    A JTableContent object
	 * @param   bool    $isNew    If the content is just about to be created
	 *
	 * @return	boolean
	 */
	public function onContentAfterSave($context, $event, $isNew)
	{
		// Autotweet Advanced Attrs
		parent::onContentAfterSave($context, $event, $isNew);

		if ($context != 'com_jem.event')
		{
			return true;
		}

		if ((!$this->on_new_event) && (!$this->on_update_event))
		{
			return;
		}

		// Not updates and it is not new
		if ((!$this->on_update_event) && (!$isNew))
		{
			return;
		}

		if (!$event->published)
		{
			return true;
		}

		if (($this->post_featured_only) && (!$event->featured))
		{
			return true;
		}

		list($catIds, $catNames) = $this->getCategories($event->id);

		$categories = array();
		$categories[] = $catIds;

		if ( (!$this->isCategoryIncluded($categories)) || ($this->isCategoryExcluded($categories)) )
		{
			return true;
		}

		$id = $event->id;
		$title = $event->title;
		$begin = $event->dates . ' ' . $event->times;
		$end = $event->enddates . ' ' . $event->endtimes;
		$image_url = $this->getImageFromText($event->introtext);
		$message = $this->template_event;

		$message = str_replace('[event]', $title, $message);
		$message = str_replace('[title]', $title, $message);
		$message = str_replace('[introtext]', $event->introtext, $message);
		$message = str_replace('[begin]', JHtml::_('date', $event->begin, $this->date_format), $message);
		$message = str_replace('[end]', JHtml::_('date', $event->end, $this->date_format), $message);

		$url = JEMHelperRoute::getEventRoute($event->alias);
		$native_object = json_encode($event);

		$this->postStatusMessage($id, $date, $message, self::TYPE_EVENT, $url, $image_url, $native_object);
	}

	/**
	 * onContentAfterSave
	 *
	 * @param   object  $context  The context of the content passed to the plugin.
	 * @param   array   $pks      A list of primary key ids of the content that has changed state.
	 * @param   int     $value    The value of the state that the content has been changed to.
	 *
	 * @return	boolean
	 */
	public function onContentChangeState($context, $pks, $value)
	{
		if (($context != 'com_jem.event') || (!$value) || (!$this->post_changestatepublished))
		{
			return true;
		}

		foreach ($pks as $id)
		{
			$event = $this->loadEvent($id);
			$this->onContentAfterSave('com_jem.event', $event, $this->post_changestatepublished);
		}

		return true;
	}

	/**
	 * getCategories
	 *
	 * @param   int  $id  Param
	 *
	 * @return	string
	 */
	private function getCategories($id)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select(array('c.id', 'c.catname'));
		$query->from($db->quoteName('#__jem_categories') . ' AS c');
		$query->join('INNER', '#__jem_cats_event_relations AS rel ON rel.catid = c.id');
		$query->where('rel.itemid = ' . $db->quote($id));

		$db->setQuery($query);
		$cats = $db->loadObjectList();

		$cat_ids = array();
		$cat_names = array();
		$cat_alias = array();

		if (!empty($cats))
		{
			foreach ($cats as $row)
			{
				$cat_ids[] = $row->id;
				$cat_names[] = $row->catname;
			}
		}

		return array(
						$cat_ids,
						$cat_names,
						$cat_alias
		);
	}

	/**
	 * loadEvent
	 *
	 * @param   string  $eventId  Param.
	 *
	 * @return	object
	 */
	public function loadEvent($eventId)
	{
		// Get data
		$db 	  = JFactory::getDBO();
		$query	  = $db->getQuery(true);

		$case_when  = ' CASE WHEN ';
		$case_when .= $query->charLength('a.alias');
		$case_when .= ' THEN ';
		$id = $query->castAsChar('a.id');
		$case_when .= $query->concatenate(array($id, 'a.alias'), ':');
		$case_when .= ' ELSE ';
		$case_when .= $id . ' END as slug';

		$query->select(array('a.*', $case_when));
		$query->select($query->concatenate(array('a.introtext', 'a.fulltext')) . ' AS text');
		$query->select(array('v.venue', 'v.city'));
		$query->from($db->quoteName('#__jem_events') . ' AS a');
		$query->join('LEFT', '#__jem_venues AS v ON v.id = a.locid');
		$query->where(array('a.id = ' . $db->quote($eventId)));

		$db->setQuery($query);

		if (is_null($event = $db->loadObject()))
		{
			return false;
		}

		return $event;
	}

	/**
	 * getExtendedData
	 *
	 * @param   string  $id              Param.
	 * @param   string  $typeinfo        Param.
	 * @param   string  &$native_object  Param.
	 *
	 * @return	array
	 */
	public function getExtendedData($id, $typeinfo, &$native_object)
	{
		$message = $this->message;

		// Property
		$event = json_decode($native_object);

		$title = $event->title;
		$introtext = $event->introtext;
		$fulltext = $event->introtext;
		$userid = $event->created_by;
		$author = $this->getAuthorUsername($userid);
		$language = null;
		$access = null;
		$url = null;

		// Get category
		list($catIds, $catNames) = $this->getCategories($event->id);

		// Use article title or text as message
		$introtext = $this->getMessagetext($this->use_text, $this->use_text_count, $message, $introtext);

		// Use metakey or static text or nothing
		if (($this->static_text_source == self::STATIC_TEXT_SOURCE_STATIC)
			|| (($this->static_text_source == self::STATIC_TEXT_SOURCE_METAKEY) && (empty($meta))))
		{
			$title = $this->addStatictext($this->static_text_pos, $title, $this->static_text);
			$introtext = $this->addStatictext($this->static_text_pos, $introtext, $this->static_text);
		}
		elseif ($this->static_text_source == self::STATIC_TEXT_SOURCE_METAKEY)
		{
			$meta = $event->metadescription;
			$this->addHashtags($this->getHashtags($meta, $this->metakey_count));
		}

		// Return values
		$data = array(
						'title' => $title,
						'text' => $introtext,
						'hashtags' => $this->renderHashtags(),
						'fulltext' => $fulltext,
						'catids' => $catIds,
						'cat_names' => $catNames,
						'author' => $author,
						'language' => $language,
						'access' => $access,

						'url' => $url,

						'is_valid' => true
		);

		return $data;
	}
}
