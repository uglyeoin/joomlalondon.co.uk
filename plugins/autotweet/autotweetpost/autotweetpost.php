<?php

/**
 * @package     Extly.Components
 * @subpackage  autotweetpost - Plugin AutoTweetNG Post-Extension
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
	JError::raiseWarning('5', 'AutoTweet NG Component is not installed or not enabled. - ' . __FILE__);

	return;
}

include_once JPATH_ADMINISTRATOR . '/components/com_autotweet/helpers/autotweetbase.php';

/**
 * PlgAutotweetAutotweetPost
 *
 * @package     Extly.Components
 * @subpackage  com_autotweet
 * @since       1.0
 */
class PlgAutotweetAutotweetPost extends PlgAutotweetBase
{
	// Typeinfo
	const TYPE_POST = 2;

	// Plugin params
	protected $categories = '';

	protected $excluded_categories = '';

	protected $post_modified = 0;

	protected $show_category = 0;

	protected $show_hash = 0;

	protected $use_text = 0;

	protected $use_text_count;

	protected $static_text = '';

	protected $static_text_pos = 1;

	protected $static_text_source = 0;

	protected $metakey_count = 1;

	protected $interval = 60;

	protected $accesslevels = '';

	/**
	 * plgContentAutotweetPost
	 *
	 * @param   string  &$subject  Param
	 * @param   object  $params    Param
	 */
	public function __construct(&$subject, $params)
	{
		parent::__construct($subject, $params);

		$pluginParams = $this->pluginParams;

		// Joomla article specific params
		$this->categories = $pluginParams->get('categories', '');
		$this->excluded_categories = $pluginParams->get('excluded_categories', '');
		$this->post_modified = (int) $pluginParams->get('post_modified', 0);
		$this->show_category = (int) $pluginParams->get('show_category', 0);
		$this->show_hash = (int) $pluginParams->get('show_hash', 0);
		$this->use_text = (int) $pluginParams->get('use_text', 0);
		$this->use_text_count = $pluginParams->get('use_text_count75', SharingHelper::MAX_CHARS_TITLE);
		$this->static_text = strip_tags($pluginParams->get('static_text', ''));
		$this->static_text_pos = (int) $pluginParams->get('static_text_pos', 1);
		$this->static_text_source = (int) $pluginParams->get('static_text_source', 0);
		$this->metakey_count = (int) $pluginParams->get('metakey_count', 1);
		$this->interval = (int) $pluginParams->get('interval', 60);

		// Correct value if value is under the minimum
		if ($this->interval < 60)
		{
			$this->interval = 60;
		}
	}

	/**
	 * postArticle
	 *
	 * @param   object  $article  The item object.
	 *
	 * @return	boolean
	 */
	public function postArticle($article)
	{
		$logger = AutotweetLogger::getInstance();
		$logger->log(JLog::INFO, 'Manual Post', $article);

		$xtform = json_decode($article['params']);

		$cats = $this->getContentCategories($xtform->catid);
		$catIds = $cats[0];

		$isIncluded = $this->isCategoryIncluded($catIds);
		$isExcluded = $this->isCategoryExcluded($catIds);

		if ((!$isIncluded) || ($isExcluded))
		{
			return true;
		}

		if (!$this->enabledAccessLevel($this->accesslevels))
		{
			return true;
		}

		if ((AUTOTWEETNG_JOOCIAL) && ($article['autotweet_advanced_attrs']))
		{
			$this->advanced_attrs = AdvancedattrsHelper::retrieveAdvancedAttrs($article['autotweet_advanced_attrs']);

			if (isset($this->advanced_attrs->ref_id))
			{
				// Safe to save
				$this->saveAdvancedAttrs($this->advanced_attrs->ref_id);
				unset($article['autotweet_advanced_attrs']);
			}
		}

		$params = null;

		if (array_key_exists('params', $article))
		{
			$params = $article['params'];
		}

		// To avoid duplication
		unset($article['id']);
		$native_object = json_encode($article);

		if (empty($article['plugin']))
		{
			$article['plugin'] = 'autotweetpost';
		}

		$this->_name = $article['plugin'];

		// $this->content_language = $article['language'];

		return $this->postStatusMessage(
				$article['ref_id'],
				$article['publish_up'],
				$article['description'],
				self::TYPE_POST,
				$article['url'],
				$article['image_url'],
				$native_object,
				$params
		);
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
		$request = $this->loadRequest($id);

		// Get category path for article
		$cats = $this->getContentCategories($request->xtform->get('catid'));
		$catIds = $cats[0];
		$catNames = $cats[1];

		// Needed for url only
		$catAlias = $cats[2];

		// Use article title or text as message
		$message = $this->message;
		$title = $message;
		$fulltext = $request->xtform->get('fulltext');
		$text = $this->getMessagetext($this->use_text, $this->use_text_count, $message, $fulltext);

		// Use metakey or static text or nothing
		if (($this->static_text_source == self::STATIC_TEXT_SOURCE_STATIC) || (($this->static_text_source == self::STATIC_TEXT_SOURCE_METAKEY) && (empty($request->metakey))))
		{
			$title = $this->addStatictext($this->static_text_pos, $title, $this->static_text);
			$text = $this->addStatictext($this->static_text_pos, $text, $this->static_text);
		}
		elseif ($this->static_text_source == self::STATIC_TEXT_SOURCE_METAKEY)
		{
			$this->addHashtags($this->getHashtags($request->xtform->get('metakey'), $request->xtform->get('metakey_count')));
		}

		// Title
		$result = $this->addCategories($this->show_category, $catNames, $title, 0);
		$title = $result['text'];

		// Text
		$result = $this->addCategories($this->show_category, $catNames, $text, $this->show_hash);
		$text = $result['text'];

		if (!empty($result['hashtags']))
		{
			$this->addHashtags($result['hashtags']);
		}

		$title = str_replace('|CR|', ' ', $title);

		$data = array(
						'title' => $title,
						'text' => $text,
						'hashtags' => $this->renderHashtags(),
						'fulltext' => $fulltext,
						'catids' => $catIds,
						'cat_names' => $catNames,
						'author' => $request->xtform->get('author'),
						'language' => $request->xtform->get('language'),
						'access' => $request->xtform->get('access'),
						'is_valid' => true
		);

		return $data;
	}
}
