<?php

/**
 * @package     Extly.Components
 * @subpackage  autotweetcontent - Plugin AutoTweetNG Content-Extension
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
 * PlgSystemAutotweetContent
 *
 * @package     Extly.Components
 * @subpackage  com_autotweet
 * @since       1.0
 */
class PlgSystemAutotweetContent extends PlgAutotweetBase
{
	// Typeinfo
	const TYPE_ARTICLE = 1;

	// Plugin params
	protected $categories = '';

	protected $excluded_categories = '';

	protected $post_modified = 0;

	protected $post_changestatepublished = 0;

	protected $show_category = 0;

	protected $show_hash = 0;

	protected $tags_as_hash = 0;

	protected $use_text = 0;

	protected $use_text_count;

	protected $static_text = '';

	protected $static_text_pos = 1;

	protected $static_text_source = 0;

	protected $metakey_count = 1;

	protected $accesslevels = '';

	protected $interval = 60;

	protected $post_old = 0;

	protected $post_old_date = '0000-00-00';

	protected $polling = 0;

	// -1 means: nothing special to do
	private $postModifiedAsNew = -1;

	/**
	 * plgSystemAutotweetContent
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
		$this->post_changestatepublished = (int) $pluginParams->get('post_changestatepublished', 1);
		$this->show_category = (int) $pluginParams->get('show_category', 0);
		$this->show_hash = (int) $pluginParams->get('show_hash', 0);
		$this->tags_as_hash = (int) $pluginParams->get('tags_as_hash', 0);
		$this->use_text = (int) $pluginParams->get('use_text', 0);
		$this->use_text_count = $pluginParams->get('use_text_count75', SharingHelper::MAX_CHARS_TITLE);
		$this->static_text = strip_tags($pluginParams->get('static_text', ''));
		$this->static_text_pos = (int) $pluginParams->get('static_text_pos', 1);
		$this->static_text_source = (int) $pluginParams->get('static_text_source', 0);
		$this->metakey_count = (int) $pluginParams->get('metakey_count', 1);
		$this->accesslevels = $pluginParams->get('accesslevels', '');
		$this->interval = (int) $pluginParams->get('interval', 180);
		$this->post_old = (int) $pluginParams->get('post_old', 0);
		$this->post_old_date = $pluginParams->get('post_old_date', '0000-00-00');
		$this->polling = (int) $pluginParams->get('polling', 0);
		$this->post_featured_only = (int) $pluginParams->get('post_featured_only', 0);

		$this->template_msg = $this->params->get('template_msg', '[title]');

		// Correct value if value is under the minimum
		if ($this->interval < 180)
		{
			$this->interval = 180;
		}

		$this->extension_option = 'com_content';
	}

	/**
	 * onContentAfterSave
	 *
	 * @param   object  $context  The context of the content passed to the plugin.
	 * @param   object  $article  A JTableContent object
	 * @param   bool    $isNew    If the content is just about to be created
	 *
	 * @return	boolean
	 */
	public function onContentAfterSave($context, $article, $isNew)
	{
		// Autotweet Advanced Attrs
		parent::onContentAfterSave($context, $article, $isNew);

		if ((($context == 'com_content.article') || ($context == 'com_content.form'))
			&& (($isNew)
			|| ($this->post_modified)
			|| ($this->postModifiedAsNew == $article->id)
			|| (($this->advanced_attrs)
			&& ( ($this->advanced_attrs->postthis == self::POSTTHIS_YES)
			|| ($this->advanced_attrs->postthis == self::POSTTHIS_IMMEDIATELY) ) ))
			&& (($article->featured > 0) || (0 == $this->post_featured_only))
			&& (1 == $article->state))
		{
			$this->postArticle($article);
		}

		return true;
	}

	/**
	 * onContentAfterDelete
	 *
	 * @param   string  $context  The context of the content passed to the plugin (added in 1.6).
	 * @param   object  $article  A JTableContent object.
	 *
	 * @return  void
	 */
	public function onContentAfterDelete($context, $article)
	{
		if ($context == 'com_content.article')
		{
			$this->cancelMessages($article->id);
		}

		return true;
	}

	/**
	 * onContentChangeState
	 *
	 * @param   object  $context  The context of the content passed to the plugin.
	 * @param   array   $pks      A list of primary key ids of the content that has changed state.
	 * @param   int     $value    The value of the state that the content has been changed to.
	 *
	 * @return	boolean
	 */
	public function onContentChangeState($context, $pks, $value)
	{
		// Content article
		if ((($context == 'com_content.article') || ($context == 'com_content.form')) && ($value == 1) && ($this->post_changestatepublished))
		{
			$article = JTable::getInstance('content');

			foreach ($pks as $id)
			{
				$article->load($id);
				$this->postArticle($article);
			}
		}

		return true;
	}

	/**
	 * postArticle
	 *
	 * @param   object  $article  The item object.
	 *
	 * @return	boolean
	 */
	protected function postArticle($article)
	{
		$cats = $this->getContentCategories($article->catid);
		$catIds = $cats[0];

		$isIncluded = $this->isCategoryIncluded($catIds);
		$isExcluded = $this->isCategoryExcluded($catIds);

		if ((!$isIncluded) || ($isExcluded))
		{
			return true;
		}

		if (!$this->enabledAccessLevel($article->access))
		{
			return true;
		}

		$catAlias = $cats[2];

		// Use main category for article url
		$cat_slug = $catIds[0] . ':' . TextUtil::convertUrlSafe($catAlias[0]);
		$id_slug = $article->id . ':' . TextUtil::convertUrlSafe($article->alias);

		// Create internal url for Joomla core content article
		JLoader::import('components.com_content.helpers.route', JPATH_ROOT);
		$url = ContentHelperRoute::getArticleRoute($id_slug, $cat_slug);

		// Get the first image from the text
		$fulltext = $article->introtext . ' ' . $article->fulltext;

		$images = null;

		if (isset($article->images))
		{
			$images = json_decode($article->images);
		}

		if (($images) && (isset($images->image_intro)) && (!empty($images->image_intro)))
		{
			$image_url = $images->image_intro;
		}
		elseif (($images) && (isset($images->image_fulltext)) && (!empty($images->image_fulltext)))
		{
			$image_url = $images->image_fulltext;
		}
		else
		{
			$image_url = $this->getImageFromText($fulltext);
		}

		$native_object = json_encode($article);

		$message = $this->template_msg;

		if (isset($article->title))
		{
			$message = str_replace('[title]', $article->title, $message);
		}

		if (isset($article->alias))
		{
			$message = str_replace('[alias]', $article->alias, $message);
		}

		if (isset($article->introtext))
		{
			$message = str_replace('[introtext]', $article->introtext, $message);
		}

		// Extra Fields Processing
		$pattern = '/\[cf_([a-z0-9\-]+)\]/i';

		if (preg_match_all($pattern, $message, $matches))
		{
			$fields = FieldsHelper::getFields('com_content.article', $article, true);
			$fieldKeys = $matches[1];

			foreach ($fields as $field)
			{
				if (!in_array($field->name, $fieldKeys))
				{
					continue;
				}

				$message = str_replace('[cf_' . $field->name . ']', $field->value, $message);
			}
		}

		$this->content_language = $article->language;
		$this->postStatusMessage($article->id, $article->publish_up, $message, self::TYPE_ARTICLE, $url, $image_url, $native_object);
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
		$article = json_decode($native_object);

		// Get category path for article
		$cats = $this->getContentCategories($article->catid);
		$catIds = $cats[0];
		$catNames = $cats[1];

		// Needed for url only
		$catAlias = $cats[2];

		// Use article title or text as message
		$message = $this->message;
		$title = $article->title;
		$articleText = $article->introtext . ' ' . $article->fulltext;
		$text = $this->getMessagetext($this->use_text, $this->use_text_count, $message, $articleText);

		// Use metakey or static text or nothing
		if (($this->static_text_source == self::STATIC_TEXT_SOURCE_STATIC)
			|| (($this->static_text_source == self::STATIC_TEXT_SOURCE_METAKEY) && (empty($article->metakey))))
		{
			$title = $this->addStatictext($this->static_text_pos, $title, $this->static_text);
			$text = $this->addStatictext($this->static_text_pos, $text, $this->static_text);
		}
		elseif ($this->static_text_source == self::STATIC_TEXT_SOURCE_METAKEY)
		{
			$this->addHashtags($this->getHashtags($article->metakey, $this->metakey_count));
		}

		// Title
		$categoriesResult = $this->addCategories($this->show_category, $catNames, $title, 0);
		$title = $categoriesResult['text'];

		// Text
		$categoriesResult = $this->addCategories($this->show_category, $catNames, $text, $this->show_hash);
		$text = $categoriesResult['text'];

		if (!empty($categoriesResult['hashtags']))
		{
			$this->addHashtags($categoriesResult['hashtags']);
		}

		if ($this->tags_as_hash)
		{
			$tags = $this->getHashtagsFromTags($id);

			if ($tags)
			{
				$this->addHashtags($tags);
			}
		}

		$data = array(
						'title' => $title,
						'text' => $text,
						'hashtags' => $this->renderHashtags(),

						// Already done when msg is inserted in queue
						// 'url' => '',

						// Already done when msg is inserted in queue
						// 'image_url' => '',

						'fulltext' => $articleText,
						'catids' => $catIds,
						'cat_names' => $catNames,
						'author' => $this->getArticleAuthor($article),
						'language' => $article->language,
						'access' => $article->access,
						'featured' => ($article->featured ? true : false),

						'is_valid' => true
		);

		return $data;
	}

	/**
	 * getHashtagsFromTags
	 *
	 * @param   int  $id  Param.
	 *
	 * @return	string
	 */
	protected function getHashtagsFromTags($id)
	{
		jimport('cms.helper.tags');
		$jtags = new JHelperTags;
		$tags = $jtags->getItemTags('com_content.article', $id);

		if (count($tags) > 0)
		{
			$titles = array_map(
					function ($v)
					{
						return $v->title;
					},
					$tags
			);
			$c = count($titles);
			$tags = implode(',', $titles);

			return $this->getHashtags($tags, $c);
		}

		return null;
	}

	/**
	 * onAfterRender
	 *
	 * @return	void
	 */
	public function onAfterRender()
	{
		$app = JFactory::getApplication();

		// Get the response body .... an additional check for J! 3.0.0
		if (method_exists($app, 'getBody'))
		{
			$body = $app->getBody();
		}
		else
		{
			$body = JResponse::getBody();
		}

		if (class_exists('Extly'))
		{
			Extly::insertDependencyManager($body);
		}

		if (method_exists($app, 'setBody'))
		{
			$app->setBody($body);
		}
		else
		{
			JResponse::setBody($body);
		}

		$this->onContentPolling();
	}
}
