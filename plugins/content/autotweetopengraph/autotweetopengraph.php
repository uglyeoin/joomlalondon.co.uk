<?php

/**
 * @package     Extly.Components
 * @subpackage  PlgContentAutotweetOpenGraph - Plugin AutoTweet NG OpenGraph Tags-Extension for Joomla!
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
	JError::raiseWarning('5', 'AutoTweet Facebook OpenGraph Tags - AutoTweet NG Component is not installed or not enabled.');

	return;
}

require_once JPATH_ADMINISTRATOR . '/components/com_autotweet/helpers/autotweetbase.php';
require_once 'opengraph.php';

/**
 * PlgContentAutotweetOpenGraph class.
 *
 * @package     Extly.Plugins
 * @subpackage  autotweetopengraph
 * @since       1.0
 */
class PlgContentAutotweetOpenGraph extends JPlugin
{
	protected $contentItem = null;

	protected $ogTagsProcessed = false;

	const OPT_OFF = 0;

	const OPT_TITLE_ARTICLE = 1;

	const OPT_TITLE_CUSTOM = 2;

	const OPT_TYPE_ARTICLE = 1;

	const OPT_TYPE_WEBSITE = 2;

	const OPT_TYPE_BOOK = 3;

	const OPT_TYPE_PROFILE = 4;

	const OPT_TYPE_CUSTOM = 5;

	const OPT_IMG_PRIO_FIRST = 1;

	const OPT_IMG_PRIO_INTRO = 2;

	const OPT_IMG_PRIO_FULL = 3;

	const OPT_IMG_PRIO_CLASS = 4;

	const OPT_IMG_PRIO_CUSTOM = 5;

	const OPT_LOCALE_SITE = 1;

	const OPT_LOCALE_ARTICLE = 2;

	const OPT_LOCALE_CUSTOM = 3;

	const OPT_SITENAME_SITE = 1;

	const OPT_SITENAME_CUSTOM = 2;

	const OPT_AUTHOR_ARTICLE = 1;

	const OPT_AUTHOR_CUSTOM = 2;

	const OPT_DESC_META = 1;

	const OPT_DESC_INTRO = 2;

	const OPT_DESC_SITE = 3;

	const OPT_DESC_CUSTOM = 4;

	const OPT_DESC_TITLE = 5;

	/**
	 * Method is called by the view
	 *
	 * @param   string   $context     The context of the content passed to the plugin
	 * @param   object   &$article    Content object. Note $article->text is also available
	 * @param   object   &$params     Content params
	 * @param   integer  $limitstart  The 'page' number
	 *
	 * @return	void
	 */
	public function onContentPrepare($context, &$article, &$params, $limitstart=0)
	{
		if ($this->ogTagsProcessed)
		{
			return;
		}

		$input = JFactory::getApplication()->input;
		$component = $input->get('option');

		$includedComponents = $this->params->get('included_component', 'com_content,com_easyblog,com_flexicontent,com_k2,com_zoo,com_autotweet');
		$includedComponents = explode(',', str_replace(array('\n', ' '), array(',', ''), $includedComponents));

		if (!in_array($component, $includedComponents))
		{
			$this->ogTagsProcessed = true;

			return;
		}

		$id = (int) $input->get('id');

		// Content - Front
		$id = $id ? $id : (int) $input->get('a_id');

		// EasyBlog
		$id = $id ? $id : (int) $input->get('blogid');

		// EasyBlog 5
		$id = $id ? $id : (int) $input->get('uid');

		// JoomShopping
		$id = $id ? $id : (int) $input->get('product_id');

		// SobiPro
		$id = $id ? $id : (int) $input->get('sid');

		// Zoo - Front
		$id = $id ? $id : (int) $input->get('item_id');

		if (!$id)
		{
			$this->ogTagsProcessed = true;

			return;
		}

		if ($articleHelper = OGArticleFactory::getHelper($component, $context, $article))
		{
			$this->contentItem = $articleHelper->getArticle();
		}

		if ($this->params->get('smart-loader', 1))
		{
			$ogSmartLoader = new OGSmartLoader($this->contentItem);

			if ($component == 'com_autotweet')
			{
				$this->contentItem = $ogSmartLoader->getItemByPost($id);
			}
			else
			{
				$this->contentItem = $ogSmartLoader->getItemByUrl();
			}
		}

		$this->insertOpenGraphTags();
		$this->ogTagsProcessed = true;
	}

	/**
	 * prepareOpenGraph
	 *
	 * @return	void
	 */
	protected function insertOpenGraphTags()
	{
		$opengraphHelper = new OpengraphHelper;
		$opengraphHelper->title = $this->title();
		$opengraphHelper->type = $this->type();
		$opengraphHelper->description = $this->desc();
		$opengraphHelper->locale = $this->locale();
		$opengraphHelper->siteName = $this->siteName();
		$opengraphHelper->author = $this->author();
		$opengraphHelper->imgSrc = $this->image();
		$opengraphHelper->url = $this->url();
		$opengraphHelper->timePublish = $this->timePublish();
		$opengraphHelper->timeModified = $this->timeModified();
		$opengraphHelper->section = $this->section();
		$opengraphHelper->fbAppId = $this->fbAppID();
		$opengraphHelper->fbPages = $this->fbPages();

		// No image yet, but we have the default image
		if (empty($opengraphHelper->imgSrc))
		{
			$imageUrl = EParameter::getComponentParam(CAUTOTWEETNG, 'default_image', '');
			$opengraphHelper->imgSrc = RouteHelp::getInstance()->getAbsoluteUrl($imageUrl, true);
		}

		$opengraphHelper->insertTags();
	}

	/**
	 * title
	 *
	 * @return	string
	 */
	protected function title()
	{
		$title = '';

		switch ($this->params->get('og-title', 1))
		{
			case self::OPT_TITLE_ARTICLE :
				$title = $this->contentItem->title;
				break;
			case self::OPT_TITLE_CUSTOM :
				$title = $this->params->get('og-title-custom');
				break;
			default:
				break;
		}

		return $title;
	}

	/**
	 * type
	 *
	 * @return	string
	 */
	protected function type()
	{
		$type = '';

		switch ($this->params->get('og-type', 1))
		{
			case self::OPT_TYPE_ARTICLE:
				$type = 'article';
				break;
			case self::OPT_TYPE_WEBSITE:
				$type = 'website';
				break;
			case self::OPT_TYPE_BOOK:
				$type = 'book';
				break;
			case self::OPT_TYPE_PROFILE:
				$type = 'profile';
				break;
			case self::OPT_TYPE_CUSTOM:
				$type = $this->params->get('og-type-custom');
				break;
			default:
				break;
		}

		return $type;
	}

	/**
	 * desc
	 *
	 * @return	string
	 */
	protected function desc()
	{
		$desc = '';

		switch ($this->params->get('og-desc', 1))
		{
			case self::OPT_DESC_META:
				$desc = $this->contentItem->metadesc;
				break;
			case self::OPT_DESC_INTRO:
			case self::OPT_DESC_SITE:
				if (empty($this->contentItem->introtext))
				{
					$joomlaConfig = JFactory::getConfig();
					$joomlaSiteName = $joomlaConfig->get('MetaDesc');
					$desc = $joomlaSiteName;
				}
				else
				{
					$desc = $this->contentItem->introtext;
				}

				break;
			case self::OPT_DESC_CUSTOM:
				$desc = $this->params->get('og-desc-custom');
				break;

			case self::OPT_DESC_TITLE:
				$desc = $this->contentItem->title;
				break;

			default:
				break;
		}

		$desc = TextUtil::cleanText($desc);
		$desc = TextUtil::truncString($desc, 512);

		return $desc;
	}

	/**
	 * locale
	 *
	 * @return	string
	 */
	private function locale()
	{
		$language = JFactory::getLanguage();
		$locale = str_replace("-", "_", $language->getTag());

		switch ($this->params->get('og-locale', 1))
		{
			case self::OPT_LOCALE_CUSTOM:
				$locale = str_replace("-", "_", $this->params->get('og-locale-custom'));
				break;
		}

		return $locale;
	}

	/**
	 * siteName
	 *
	 * @return	string
	 */
	protected function siteName()
	{
		$joomlaConfig = JFactory::getConfig();
		$siteName = $joomlaConfig->get('sitename');

		switch ($this->params->get('og-sitename', 1))
		{
			case self::OPT_SITENAME_CUSTOM:
				$siteName = $this->params->get('og-sitename-custom');
				break;
			default:
				break;
		}

		return $siteName;
	}

	/**
	 * author
	 *
	 * @return	string
	 */
	protected function author()
	{
		$author = $this->getAuthor();

		switch ($this->params->get('og-author', 1))
		{
			case self::OPT_AUTHOR_CUSTOM:
				$author = $this->params->get('og-author-custom');
				break;
			default:
				break;
		}

		return $author;
	}

	/**
	 * getImageWithClass
	 *
	 * @param   string  $images    Param
	 * @param   string  $cssClass  Param
	 *
	 * @return	string
	 */
	protected function getImageWithClass($images, $cssClass)
	{
		if ( (!isset($images)) || (empty ($images))  )
		{
			return;
		}

		foreach ($images as $image)
		{
			$classes = explode(' ', $image['class']);

			if (in_array($cssClass, $classes))
			{
				return $image['src'];
			}
		}
	}

	/**
	 * imageWithOption
	 *
	 * @param   string  $option  Param
	 *
	 * @return	string
	 */
	protected function imageWithOption($option)
	{
		switch ($option)
		{
			case self::OPT_IMG_PRIO_FIRST:
				$image = (isset($this->contentItem->firstContentImage)) ? $this->contentItem->firstContentImage : '';
				break;
			case self::OPT_IMG_PRIO_INTRO:
				$image = (isset($this->contentItem->introImage)) ? $this->contentItem->introImage : '';
				break;
			case self::OPT_IMG_PRIO_FULL:
				$image = (isset($this->contentItem->fullTextImage)) ? $this->contentItem->fullTextImage : '';
				break;
			case self::OPT_IMG_PRIO_CLASS:
				$images = (!empty($this->contentItem->imageArray)) ? $this->contentItem->imageArray : null;
				$cssClass = $this->params->get('og-img-class', 'xt-image');
				$classImage = $this->getImageWithClass($images, $cssClass);
				$image = (isset($classImage)) ? $classImage : '';
				break;
			case self::OPT_IMG_PRIO_CUSTOM:
				if ($this->params->get('og-img-custom') != '')
				{
					$image = 'images/' . $this->params->get('og-img-custom');
				}
				break;
			default:
			return;
				break;
		}

		if (!empty($image))
		{
			$image = RouteHelp::getInstance()->getAbsoluteUrl($image, true);

			return $image;
		}
	}

	/**
	 * image
	 *
	 * @return	string
	 */
	protected function image()
	{
		$image = $this->imageWithOption($this->params->get('og-img-prio1', 2));

		if (empty($image))
		{
			$image = $this->imageWithOption($this->params->get('og-img-prio2', 3));
		}

		if (empty($image))
		{
			$image = $this->imageWithOption($this->params->get('og-img-prio3', 1));
		}

		return $image;
	}

	/**
	 * url
	 *
	 * @return	string
	 */
	protected function url()
	{
		$url = $this->contentItem->url;

		return $url;
	}

	/**
	 * timePublish
	 *
	 * @return	string
	 */
	protected function timePublish()
	{
		return date('c', strtotime($this->contentItem->publish_up));
	}

	/**
	 * timeModified
	 *
	 * @return	string
	 */
	protected function timeModified()
	{
		return date('c', strtotime($this->contentItem->modified));
	}

	/**
	 * section
	 *
	 * @return	string
	 */
	protected function section()
	{
		return $this->contentItem->category_title;
	}

	/**
	 * author
	 *
	 * @return	string
	 */
	protected function getAuthor()
	{
		$user = JUser::getInstance($this->contentItem->created_by);

		return $user->name;
	}

	/**
	 * fbAppID
	 *
	 * @return	string
	 */
	protected function fbAppID()
	{
		return $this->params->get('og-fbappid');
	}

	/**
	 * fbPages
	 *
	 * @return	string
	 */
	protected function fbPages()
	{
		return $this->params->get('og-fbpages');
	}
}
