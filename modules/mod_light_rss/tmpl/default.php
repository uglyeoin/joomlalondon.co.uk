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

// Error handling: output any error message to admin users only

if ((array_key_exists('error', $light_rss)) && (count($light_rss['error'])))
{
	// Only show errors to admin group users
	print '<div class="text-error"><b>Error(s):</b><ul style="margin-left:4px;padding-left:4px;">';

	foreach ($light_rss['error'] as $error)
	{
		print '<li>' . $error . '</li>';
	}

	print '</ul></div>';
}

// Check to enable tooltip js for lightTip classed elements
if ($params->get('enable_tooltip') == 'yes')
{
	JHTML::_('behavior.tooltip', '.lightTip');
	$tooltips = true;
}

// Begin output
// Based on light rss http://joomla.daveslist.co.nz
?>
<div class="light-rss-container" style="direction: <?php
		echo $rssrtl ? 'rtl' : 'ltr';
	?>; text-align: <?php
		echo $rssrtl ? 'right' : 'left';
	?>">
	<?php

	// Feed title
	if ($params->get('rsstitle', 1) && $light_rss['title']['link'])
	{
		print '<div><a href="' . $light_rss['title']['link'] . '" target="' . $light_rss['target'] . '">' . $light_rss['title']['title'] . '</a></div>';
	}

	// Feed desc
	if ($params->get('rssdesc', 1) && $light_rss['description'])
	{
		print '<div class="light-rss-desc">' . $light_rss['description'] . '</div>';
	}

	// Feed image
	if ($params->get('rssimage', 0) && $light_rss['image']['url'])
	{
		print '<img src="' . $light_rss['image']['url'] . '" title="' . $light_rss['image']['title'] . '" class="light-rss-img">';
	}

	?>
	<ul
		class="light-rss-list<?php echo $params->get('moduleclass_sfx'); ?>"
		style="margin-left: 0px; padding-left: 0px;">
		<?php
		if (array_key_exists('items', $light_rss))
		{
			foreach ($light_rss['items'] as $item)
			{
				$title = '';

				if ($enable_tooltip)
				{
					$title = $item['tooltip']['title'] . '::' . $item['tooltip']['description'];
				}
				else
				{
					if (array_key_exists('description', $item))
					{
						$title = $item['description'];
					}
				}

				$desc = '';

				// Item desc
				if (($params->get('rssitemdesc', 0)) && (array_key_exists('description', $item)))
				{
					$desc = $item['description'];
				}

				print '
        <li class="light-rss-item' . $params->get('moduleclass_sfx') . '">
        <a href="' . $item['link'] . '" title="' . $title . '" class="lightTip" target="' . $light_rss['target'] . '" ' . (array_key_exists('nofollow', $light_rss) ? $light_rss['nofollow'] : '') . '>' . $item['title'] . '</a>';
				print '<div class="light-rss-item-desc">' . $desc . '</div>';
				print '</li>';
			}
		}
		?>
	</ul>
</div>
