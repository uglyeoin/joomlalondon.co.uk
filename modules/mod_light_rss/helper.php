<?php

/**
 * @package     Extly.Modules
 * @subpackage  mod_light_rss - Light RSS
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

defined('_JEXEC') or die;

/**
 * ModLightRSSHelper class.
 *
 * @package     Extly.Components
 * @subpackage  mod_light_rss
 * @since       1.0
 */
class ModLightRSSHelper
{
	/**
	 * getFeed
	 *
	 * @param   array  &$params  The module options.
	 *
	 * @return	void
	 */
	public static function getFeed(&$params)
	{
		// Global $mainframe;

		// Init feed array
		$light_rss = array();

		// Get local module parameters from xml file module config settings
		$rssurl = $params->get('rssurl', null);
		$rssitems = $params->get('rssitems', 5);
		$rssdesc = $params->get('rssdesc', 1);
		$rssimage = $params->get('rssimage', 1);
		$rssitemtitle_words = $params->get('rssitemtitle_words', 0);
		$rssitemdesc = $params->get('rssitemdesc', 0);
		$rssitemdesc_images = $params->get('rssitemdesc_images', 1);
		$rssitemdesc_words = $params->get('rssitemdesc_words', 0);
		$rsstitle = $params->get('rsstitle', 1);
		$rsscache = $params->get('rsscache', 3600);
		$link_target = $params->get('link_target', 1);
		$no_follow = $params->get('no_follow', 0);
		$enable_tooltip = $params->get('enable_tooltip', 'yes');
		$tooltip_desc_words = $params->get('t_word_count_desc', 25);
		$tooltip_desc_images = $params->get('tooltip_desc_images', 1);
		$tooltip_title_words = $params->get('t_word_count_title', 25);
		$add_dots = !EParameter::getComponentParam(CAUTOTWEETNG, 'donot_add_dots');

		if (!$rssurl)
		{
			$light_rss['error'][] = 'Invalid feed url. Please enter a valid url in the module settings.';

			// Halt if no valid feed url supplied
			return $light_rss;
		}

		switch ($link_target)
		{
			// Open links in current or new window
			case 1:
				$link_target = '_blank';
				break;
			case 0:
				$link_target = '_self';
				break;
			default:
				$link_target = '_blank';
				break;
		}

		$light_rss['target'] = $link_target;

		if ($no_follow)
		{
			$light_rss['nofollow'] = 'rel="nofollow"';
		}

		if (!class_exists('SimplePie'))
		{
			// Include Simple Pie processor class
			require_once JPATH_AUTOTWEET_VENDOR . '/autoload.php';
		}

		// Load and build the feed array
		$feed = new SimplePie;

		$use_sp_cache = EParameter::getComponentParam(CAUTOTWEETNG, 'use_sp_cache', true);

		if (($use_sp_cache) && (is_writable(JPATH_CACHE)))
		{
			$feed->set_cache_location(JPATH_CACHE);
			$feed->enable_cache(true);

			$cache_time = (intval($rsscache));
			$feed->set_cache_duration($cache_time);
		}
		else
		{
			$feed->enable_cache(false);
		}

		$feed->set_feed_url($rssurl);

		// Process the loaded feed
		$feed->init();
		$feed->handle_content_type();

		// Store any error message
		if (isset($feed->error))
		{
			$light_rss['error'][] = $feed->error;
		}

		// Start building the feed meta-info (title, desc and image)
		// Feed title
		if ($feed->get_title() && $rsstitle)
		{
			$light_rss['title']['link'] = $feed->get_link();
			$light_rss['title']['title'] = $feed->get_title();
		}

		// Feed description
		if ($rssdesc)
		{
			$light_rss['description'] = $feed->get_description();
		}

		// Feed image
		if ($rssimage && $feed->get_image_url())
		{
			$light_rss['image']['url'] = $feed->get_image_url();
			$light_rss['image']['title'] = $feed->get_image_title();
		}

		// End feed meta-info

		// Start processing feed items
		// If there are items in the feed
		if ($feed->get_item_quantity())
		{
			// Start looping through the feed items
			$light_rss_item = 0;

			// Item counter for array indexing in the loop
			foreach ($feed->get_items(0, $rssitems) as $currItem)
			{
				// Item title
				$item_title = trim($currItem->get_title());

				// Item title word limit check
				if ($rssitemtitle_words)
				{
					$item_titles = explode(' ', $item_title);
					$count = count($item_titles);

					if ($count > $rssitemtitle_words)
					{
						$item_title = '';

						for ($i = 0; $i < $rssitemtitle_words; $i++)
						{
							$item_title .= ' ' . $item_titles[$i];
						}

						if ($add_dots)
						{
							$item_title .= '...';
						}
					}
				}

				// Item Title
				$light_rss['items'][$light_rss_item]['title'] = $item_title;
				$light_rss['items'][$light_rss_item]['link'] = $currItem->get_permalink();

				// Item description
				if ($rssitemdesc)
				{
					$desc = trim($currItem->get_description());

					if (!$rssitemdesc_images)
					{
						// Strip image tags
						$desc = preg_replace("/<img[^>]+\>/i", "", $desc);
					}

					// Item description word limit check
					if ($rssitemdesc_words)
					{
						$texts = explode(' ', $desc);
						$count = count($texts);

						if ($count > $rssitemdesc_words)
						{
							$desc = '';

							for ($i = 0; $i < $rssitemdesc_words; $i++)
							{
								// Build words
								$desc .= ' ' . $texts[$i];
							}

							if ($add_dots)
							{
								$desc .= '...';
							}
						}
					}

					// Item Description
					$light_rss['items'][$light_rss_item]['description'] = $desc;
				}

				// Tooltip text
				if ($enable_tooltip == 'yes')
				{
					// Tooltip item title
					$t_item_title = trim($currItem->get_title());

					// Tooltip title word limit check
					if ($tooltip_title_words)
					{
						$t_item_titles = explode(' ', $t_item_title);
						$count = count($t_item_titles);

						if ($count > $tooltip_title_words)
						{
							$tooltip_title = '';

							for ($i = 0; $i < $tooltip_title_words; $i++)
							{
								$tooltip_title .= ' ' . $t_item_titles[$i];
							}

							if ($add_dots)
							{
								$tooltip_title .= '...';
							}
						}
						else
						{
							$tooltip_title = $t_item_title;
						}
					}
					else
					{
						$tooltip_title = $t_item_title;
					}

					// Replace new line characters in tooltip title, important!
					$tooltip_title = preg_replace("/(\r\n|\n|\r)/", " ", $tooltip_title);

					// Format text for tooltip
					$tooltip_title = htmlspecialchars(html_entity_decode($tooltip_title), ENT_QUOTES);

					// Tooltip Title
					$light_rss['items'][$light_rss_item]['tooltip']['title'] = $tooltip_title;

					// Tooltip item description
					$text = trim($currItem->get_description());

					if (!$tooltip_desc_images)
					{
						$text = preg_replace("/<img[^>]+\>/i", "", $text);
					}

					// Tooltip desc word limit check
					if ($tooltip_desc_words)
					{
						$texts = explode(' ', $text);
						$count = count($texts);

						if ($count > $tooltip_desc_words)
						{
							$text = '';

							for ($i = 0; $i < $tooltip_desc_words; $i++)
							{
								$text .= ' ' . $texts[$i];
							}

							if ($add_dots)
							{
								$text .= '...';
							}
						}
					}

					// Replace new line characters in tooltip, important!
					$text = preg_replace("/(\r\n|\n|\r)/", " ", $text);

					// Format text for tooltip
					$text = htmlspecialchars(html_entity_decode($text), ENT_QUOTES);

					// Tooltip Body
					$light_rss['items'][$light_rss_item]['tooltip']['description'] = $text;
				}
				else
				{
					// Blank
					$light_rss['items'][$light_rss_item]['tooltip'] = array();
				}

				// Increment item counter
				$light_rss_item++;
			}
		}

		// End item quantity check if statement

		// Return the feed data structure for the template
		return $light_rss;
	}
}
