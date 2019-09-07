<?php

/**
 * @package     Extly.Components
 * @subpackage  PlgSystemAutotweetSocialProfile - AutoTweet Social Profile Links for Google
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2018 Extly, CB. All rights reserved.
 * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

defined('_JEXEC') or die;

/**
 * AutoTweet Social Profile Links for Google
 *
 * @package     Extly.Components
 * @subpackage  com_autotweet
 * @since       1.0
 */
class PlgSystemAutotweetSocialProfile extends JPlugin
{
	/**
	 * onBeforeRender
	 *
	 * @return array A four element array of (article_id, article_title, category_id, object)
	 */
	public function onBeforeRender()
	{
		include_once JPATH_ADMINISTRATOR . '/components/com_autotweet/helpers/autotweetbase.php';

		// Use only for front-end site.
		if (JFactory::getApplication()->isAdmin())
		{
			return;
		}

		$type = $this->params->get('type', 'Organization');
		$customtype = $this->params->get('customtype');
		$name = $this->params->get('name', JFactory::getConfig()->get('sitename'));
		$url = $this->params->get('url', JUri::root());

		$sameAsFacebook = $this->params->get('sameAsFacebook');
		$sameAsTwitter = $this->params->get('sameAsTwitter');
		$sameAsGPlus = $this->params->get('sameAsGPlus');
		$sameAsInstagram = $this->params->get('sameAsInstagram');
		$sameAsYoutube = $this->params->get('sameAsYoutube');
		$sameAsLinkedIn = $this->params->get('sameAsLinkedIn');
		$sameAsMyspace = $this->params->get('sameAsMyspace');

		$sameAsPinterest = $this->params->get('sameAsPinterest');
		$sameAsSoundCloud = $this->params->get('sameAsSoundCloud');
		$sameAsTumblr = $this->params->get('sameAsTumblr');

		$logo = $this->params->get('logo');
		$image = $this->params->get('image');

		$telephone = $this->params->get('telephone');

		$contactTelephone = $this->params->get('contactTelephone');
		$contactType = $this->params->get('contactType', 'customer support');
		$areaServed = $this->params->get('areaServed');
		$contactOption = $this->params->get('contactOption');
		$availableLanguage = $this->params->get('availableLanguage');

		$streetAddress = $this->params->get('streetAddress');
		$addressLocality = $this->params->get('addressLocality');
		$addressRegion = $this->params->get('addressRegion');
		$postalCode = $this->params->get('postalCode');
		$addressCountry = $this->params->get('addressCountry');
		$latitude = $this->params->get('latitude');
		$longitude = $this->params->get('longitude');

		$photo = $this->params->get('photo');
		$priceRange = $this->params->get('priceRange');

		$structured_markup = array();
		$sameAs = array();

		$structured_markup['@context'] = 'http://schema.org';

		if (empty($customtype))
		{
			$structured_markup['@type'] = $type;
		}
		else
		{
			$structured_markup['@type'] = $customtype;
		}


		$structured_markup['name'] = $name;
		$structured_markup['url'] = $url;

		if (!empty($logo))
		{
			$logo_url = RouteHelp::getInstance()->getAbsoluteUrl($logo, true);

			if (!empty($logo_url))
			{
				$structured_markup['logo'] = $logo_url;
			}
		}

		if (!empty($image))
		{
			$image_url = RouteHelp::getInstance()->getAbsoluteUrl($image, true);

			if (!empty($image_url))
			{
				$structured_markup['image'] = $image_url;
			}
		}

		if (!empty($telephone))
		{
			$structured_markup['telephone'] = $telephone;
		}

		if ($sameAsFacebook)
		{
			$sameAs[] = $sameAsFacebook;
		}

		if ($sameAsTwitter)
		{
			$sameAs[] = $sameAsTwitter;
		}

		if ($sameAsGPlus)
		{
			$sameAs[] = $sameAsGPlus;
		}

		if ($sameAsInstagram)
		{
			$sameAs[] = $sameAsInstagram;
		}

		if ($sameAsYoutube)
		{
			$sameAs[] = $sameAsYoutube;
		}

		if ($sameAsLinkedIn)
		{
			$sameAs[] = $sameAsLinkedIn;
		}

		if ($sameAsMyspace)
		{
			$sameAs[] = $sameAsMyspace;
		}

		if ($sameAsPinterest)
		{
			$sameAs[] = $sameAsPinterest;
		}

		if ($sameAsSoundCloud)
		{
			$sameAs[] = $sameAsSoundCloud;
		}

		if ($sameAsTumblr)
		{
			$sameAs[] = $sameAsTumblr;
		}

		if (!empty($sameAs))
		{
			$structured_markup['sameAs'] = $sameAs;
		}

		if (!empty($contactTelephone))
		{
			$contactPoint = array(
				'@type' => "ContactPoint",
				'telephone' => $contactTelephone
			);

			if (!empty($contactType))
			{
				$contactPoint['contactType'] = $contactType;
			}

			if (!empty($areaServed))
			{
				$areaServed = TextUtil::listToArray($areaServed);
				$contactPoint['areaServed'] = $areaServed;
			}

			if (!empty($contactOption))
			{
				$contactPoint['contactOption'] = $contactOption;
			}

			if (!empty($availableLanguage))
			{
				$availableLanguage = TextUtil::listToArray($availableLanguage);
				$contactPoint['availableLanguage'] = $availableLanguage;
			}

			$structured_markup['contactPoint'] = $contactPoint;
		}

		if ( (!empty($streetAddress))
			&& (!empty($addressLocality))
			&& (!empty($addressRegion))
			&& (!empty($postalCode))
			&& (!empty($addressCountry)) )
		{
			$address = array(
				'@type' => 'PostalAddress',
				'streetAddress' => $streetAddress,
				'addressLocality' => $addressLocality,
				'addressRegion' => $addressRegion,
				'postalCode' => $postalCode,
				'addressCountry' => $addressCountry
			);

			$structured_markup['address'] = $address;
		}

		if ( ($type == 'LocalBusiness') && (!empty($latitude)) && (!empty($longitude)) )
		{
			$geo = array(
				'@type' => 'GeoCoordinates',
				'latitude' => $latitude,
				'longitude' => $longitude
			);

			$structured_markup['geo'] = $geo;
		}

		if (($type == 'LocalBusiness') && (!empty($photo)))
		{
			$photo_url = RouteHelp::getInstance()->getAbsoluteUrl($photo, true);

			if (!empty($photo_url))
			{
				$structured_markup['photo'] = $photo_url;
			}
		}

		if (($type == 'LocalBusiness') && (!empty($priceRange)))
		{
			$structured_markup['priceRange'] = $priceRange;
		}

		JFactory::getDocument()->addScriptDeclaration(json_encode($structured_markup), 'application/ld+json');

		return;
	}
}
