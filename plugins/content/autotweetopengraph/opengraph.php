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

/**
 * Opengraph class.
 *
 * @package     Extly.Plugins
 * @subpackage  autotweetopengraph
 * @since       1.0
 */
class OpengraphHelper
{
	public $title;

	public $type;

	public $description;

	public $locale;

	public $siteName;

	public $imgSrc;

	public $url;

	public $timePublish;

	public $timeModified;

	public $section;

	public $author;

	public $fbAppId;

	public $fbPages;

	const OG_TITLE = 'og:title';

	const OG_TYPE = 'og:type';

	const OG_DESC = 'og:description';

	const OG_LOCALE = 'og:locale';

	const OG_SITENAME = 'og:site_name';

	const OG_IMAGE = 'og:image';

	const OG_URL = 'og:url';

	const OG_TIMEPUB = 'article:published_time';

	const OG_TIMEMOD = 'article:modified_time';

	const OG_SECTION = 'article:section';

	const OG_AUTHOR = 'article:author';

	const OG_FBAPPID = 'fb:app_id';

	const OG_FBPAGES = 'fb:pages';

	/**
	 * __construct - Class for inserting OG-tags in the HTML header
	 *
	 * @param   string  $title         Param
	 * @param   string  $type          Param
	 * @param   string  $desc          Param
	 * @param   string  $locale        Param
	 * @param   string  $siteName      Param
	 * @param   string  $img           Param
	 * @param   string  $url           Param
	 * @param   string  $timePublish   Param
	 * @param   string  $timeModified  Param
	 * @param   string  $section       Param
	 * @param   string  $author        Param
	 * @param   string  $fbAppId       Param
	 * @param   string  $fbPages       Param
	 */
	public function __construct($title='', $type='', $desc='', $locale='', $siteName='', $img='', $url='', $timePublish='', $timeModified='', $section='', $author='', $fbAppId='', $fbPages = '')
	{
		$this->title = $title;
		$this->type = $type;
		$this->description = $desc;
		$this->locale = $locale;
		$this->siteName = $siteName;
		$this->imgSrc = $img;
		$this->url = $url;
		$this->timePublish = $timePublish;
		$this->timeModified = $timeModified;
		$this->section = $section;
		$this->author = $author;
		$this->fbAppId = $fbAppId;
		$this->fbPages = $fbPages;
	}

	/**
	 * Method for creating and inserting meta tags in the html header
	 *
	 * @param   string  $property  Param
	 * @param   string  $content   Param
	 *
	 * @return	void
	 */
	public function insertTag($property, $content)
	{
		$document = JFactory::getDocument();
		$doctype    = $document->getType();

		if ($doctype !== 'html' || $content == '')
		{
			return;
		}

		$sanitizedContent = htmlentities(strip_tags($content), ENT_QUOTES, "UTF-8");
		$meta = '<meta property="' . $property . '" content="' . $sanitizedContent . '" />';
		$document->addCustomTag($meta);
	}

	/**
	 * Method for inserting tags in the HTML header
	 *
	 * @return	void
	 */
	public function insertTags()
	{
		$this->insertTag(self::OG_TITLE, $this->title);
		$this->insertTag(self::OG_TYPE, $this->type);
		$this->insertTag(self::OG_DESC, $this->description);
		$this->insertTag(self::OG_LOCALE, $this->locale);
		$this->insertTag(self::OG_SITENAME, $this->siteName);
		$this->insertTag(self::OG_IMAGE, $this->imgSrc);
		$this->insertTag(self::OG_URL, $this->url);
		$this->insertTag(self::OG_TIMEPUB, $this->timePublish);
		$this->insertTag(self::OG_TIMEMOD, $this->timeModified);
		$this->insertTag(self::OG_SECTION, $this->section);
		$this->insertTag(self::OG_AUTHOR, $this->author);
		$this->insertTag(self::OG_FBAPPID, $this->fbAppId);
		$this->insertTag(self::OG_FBPAGES, $this->fbPages);
	}
}
