<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_kou_rss_feeds_extra
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Add CSS
$document = JFactory::getDocument();

// Add a stylesheet
$mediaUrl = 'media/' . $module->module;
$cssName = 'feed.css';
if (file_exists($mediaUrl . '/css/' . $cssName) && filesize($mediaUrl . '/css/' . $cssName) > 0)
{
	$document = JFactory::getDocument();
	$document->addStyleSheetVersion($mediaUrl . '/css/' . $cssName, array('version' => 'auto'));
}


// Channel ID for J & Beyond  UCy6ThiEDnalZOd_pgtpBk1Q

// Params
$channel_id 			= $params->get('channel_id');
$youtubeChannelFeed		= "https://www.youtube.com/feeds/videos.xml?channel_id=" . $channel_id;
$playlist_id			= $params->get('playlist_id');
$youtubePlaylistFeed	= "https://www.youtube.com/feeds/videos.xml?playlist_id=" . $playlist_id;
$numberOfFeedItems 		= $params->get('Number_of_feed_items');
$feedType				= $params->get('feed_type');
$Number_of_feed_items	= $params->get('Number_of_feed_items');
$container_class		= $params->get('container_class');
$row_class				= $params->get('row_class');
$column_class			= $params->get('column_class');

if ($feedType = 0) 
	{$feedType = "RSS";}
elseif ($feedType = 1) 
	{$feedType = "YouTube";}

if ($feedType = "YouTube") { $schemaItemType = "VideoObject";}

// Build the Feed
$feed = $youtubeChannelFeed;
$xml = simplexml_load_file($feed);
$html = "";


$feedItemsCount = count($xml->items);
    if ($feedItemsCount > $Number_of_feed_items) {
        $numberOfForLoops = $feedItemsCount;
    }
	else 
	{
        $numberOfForLoops = $numberOfFeedItems;
    }
?>

<div class="<?php echo $module->module . ' ' . $container_class ?>" itemscope itemtype="http://schema.org/<?php echo $schemaItemType; ?>">

	<?php
	
	$containerCloseDiv = "";
	$rowCloseDiv = "";
	$rowDiv = '<div class="' . $row_class . '">';

	?>
	<div><h1>THIS IS THE THING</h1></div>



	<?php
	$postChunks = array_chunk((array)$xml, 4); // 4 is used to have 4 items in a row
	foreach ($postChunks as $videos) { ?>

		<h1>Videos Print ARrrghghhhhh</h1>
		<?php 
			$user = JFactory::getUser();
			$isroot = $user->authorise('core.admin');
			if($isroot) {
				highlight_string("<?php\n\$videos =\n" . var_export($videos, true) . ";\n?>");
			}
		?>

		<div class="g-grid">
		<?php
			foreach ($videos as $video) { ?>
				<div class="g-block size-25">
					
					<?php // echo $xml->entry->title; ?>
					<?php echo "Videos: " . $videos[0][0]; ?>

					<?php 
					/*
						$user = JFactory::getUser();
						$isroot = $user->authorise('core.admin');
						if($isroot) {
							highlight_string("<?php\n\$video =\n" . var_export($video, true) . ";\n?>");
						}
						*/
					?>
				</div>     
		<?php
			}
		?>
		</div>
	<?php 
	}?>

	<div><h1>THE THING HAS ENDED</h1></div>

	<?php

	for($i = 1; $i < $numberOfForLoops; $i++)
	{

		$link 						=	$xml->entry->link[0]['href'];
		$youtubeItemTitle			= 	$xml->entry->title;
		$youtubeItemLink			=  	substr($link, strpos($link, "?v=") + 3);
		$youtubeItemDate			=  	date('l, d m Y', strtotime($xml->entry->published));

		//if ($i % 5 == 0) {
		//	$rowDiv = '<div class="' . $row_class . '">';
		//}


		if ($i == 1 || $i % 5 == 0) {
			echo $rowDiv; 
		} ?>

			<div class="item<?php echo ' ' . $column_class; ?>">
			<?php echo $i; ?>
				<iframe 
					width="560" 
					height="315" 
					src="https://www.youtube.com/embed/<?php echo $youtubeItemLink; ?>"
					frameborder="0" 
					allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" 
					allowfullscreen>
				</iframe>
				<div class="ItemTitle">
					<?php echo $youtubeItemTitle; ?>
				</div>
			</div>
		<?php
			if ($i % 4 == 0) {
				echo "</div>";
			}
		?>
	<?php } ?>
</div>


<?php
	/* DEBUGING */
	$user = JFactory::getUser();
	$isroot = $user->authorise('core.admin');
	if($isroot) {
		highlight_string("<?php\n\$xml =\n" . var_export($xml, true) . ";\n?>");
	}
?>