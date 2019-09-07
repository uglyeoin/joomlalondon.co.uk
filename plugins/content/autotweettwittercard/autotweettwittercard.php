<?php

/**
 * @package     Extly.Components
 * @subpackage  PlgContentAutotweetTwitterCard - Plugin AutoTweet NG TwitterCard Tags-Extension for Joomla!
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
	JError::raiseWarning('5', 'AutoTweet NG New User-Plugin - AutoTweet NG Component is not installed or not enabled.');

	return;
}

include_once JPATH_ADMINISTRATOR . '/components/com_autotweet/helpers/autotweetbase.php';
require_once 'twittercard.php';

/**
 * PlgContentAutotweetTwitterCard class.
 *
 * @package     Extly.Plugins
 * @subpackage  autotweettwittercard
 * @since       1.0
 */
class PlgContentAutotweetTwitterCard extends JPlugin
{
	protected $contentItem;

	protected $twitterCard;

	protected $classNames;

	protected $ogTagsProcessed = false;

	const OPT_OFF = 0;

	const OPT_TITLE_ARTICLE = 1;

	const OPT_TITLE_CUSTOM = 2;

	const OPT_IMG_PRIO_FIRST = 1;

	const OPT_IMG_PRIO_INTRO = 2;

	const OPT_IMG_PRIO_FULL = 3;

	const OPT_IMG_PRIO_CLASS = 4;

	const OPT_IMG_PRIO_CUSTOM = 5;

	const OPT_AUTHOR_ARTICLE = 1;

	const OPT_AUTHOR_CUSTOM = 2;

	const OPT_DESC_META = 1;

	const OPT_DESC_INTRO = 2;

	const OPT_DESC_SITE = 3;

	const OPT_DESC_CUSTOM = 4;

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

		$includedComponents = $this->params->get('included_components', 'com_content,com_easyblog,com_flexicontent,com_k2,com_zoo');
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

		$this->insertTwitterCard();
		$this->ogTagsProcessed = true;
	}

	/**
	 * insertTwitterCard
	 *
	 * @return	void
	 */
	protected function insertTwitterCard()
	{
		$twitterCard = new TwittercardHelper;
		$twitterCard->title = $this->title();
		$twitterCard->type = $this->type();
		$twitterCard->description = $this->desc();
		$twitterCard->author = $this->author();
		$twitterCard->imgSrc = $this->image();

		// No image yet, but we have the default image
		if (empty($twitterCard->imgSrc))
		{
			$imageUrl = EParameter::getComponentParam(CAUTOTWEETNG, 'default_image', '');
			$twitterCard->imgSrc = RouteHelp::getInstance()->getAbsoluteUrl($imageUrl, true);
		}

		$twitterCard->insertTags();
	}

	/**
	 * title
	 *
	 * @return	string
	 */
	protected function title()
	{
		$title = '';

		switch ($this->params->def('og-title', 1))
		{
			case self::OPT_TITLE_ARTICLE :
				$title = $this->contentItem->title;
				break;
			case self::OPT_TITLE_CUSTOM :
				$title = $this->params->def('og-title-custom');
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
		$type = $this->params->def('og-type', 'summary_large_image');

		if ((empty($type)) || ($type == 'custom'))
		{
			$type = $this->params->def('og-type-custom', 'summary_large_image');
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

		switch ($this->params->def('og-desc', 1))
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
				$desc = $this->params->def('og-desc-custom');
				break;
			default:
				break;
		}

		$desc = TextUtil::cleanText($desc);
		$desc = TextUtil::truncString($desc, 512);

		return $desc;
	}

	/**
	 * author
	 *
	 * @return	string
	 */
	protected function author()
	{
		$author = $this->getAuthor();

		switch ($this->params->def('og-author', 1))
		{
			case self::OPT_AUTHOR_CUSTOM:
				$author = $this->params->def('og-author-custom');
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
				$cssClass = $this->params->def('og-img-class', 'xt-image');
				$classImage = $this->getImageWithClass($images, $cssClass);
				$image = (isset($classImage)) ? $classImage : '';
				break;
			case self::OPT_IMG_PRIO_CUSTOM:
				if ($this->params->def('og-img-custom') != '')
				{
					$image = 'images/' . $this->params->def('og-img-custom');
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
		$image = $this->imageWithOption($this->params->def('og-img-prio1', 2));

		if (empty($image))
		{
			$image = $this->imageWithOption($this->params->def('og-img-prio2', 3));
		}

		if (empty($image))
		{
			$image = $this->imageWithOption($this->params->def('og-img-prio3', 1));
		}

		return $image;
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
}
