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

/**
 * Opengraph class.
 *
 * @package     Extly.Plugins
 * @subpackage  autotweettwittercard
 * @since       1.0
 */
class TwittercardHelper
{
	public $title;

	public $type;

	public $description;

	public $imgSrc;

	public $author;

	const OG_TITLE = 'twitter:title';

	const OG_TYPE = 'twitter:card';

	const OG_DESC = 'twitter:description';

	const OG_AUTHOR = 'twitter:creator';

	const OG_IMAGE = 'twitter:image:src';

	/**
	 * __construct - Class for inserting OG-tags in the HTML header
	 *
	 * @param   string  $title   Param
	 * @param   string  $type    Param
	 * @param   string  $desc    Param
	 * @param   string  $img     Param
	 * @param   string  $author  Param
	 */
	public function __construct($title='', $type='', $desc='', $img='', $author='')
	{
		$this->title = $title;
		$this->type = $type;
		$this->description = $desc;
		$this->imgSrc = $img;
		$this->author = $author;
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
		$this->insertTag(self::OG_IMAGE, $this->imgSrc);
		$this->insertTag(self::OG_AUTHOR, $this->author);
	}
}
