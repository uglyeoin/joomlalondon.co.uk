<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_kou_youtube_rss_playlist_or_channel
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


// Channel ID for J & Beyond  UCy6ThiEDnalZOd_pgtpBk1Q  // https://www.youtube.com/feeds/videos.xml?channel_id=UCy6ThiEDnalZOd_pgtpBk1Q

// Params
$youTubeFeedStart		= "https://www.youtube.com/feeds/videos.xml?";
$channel_id 			= $params->get('channel_id');
$youtubeChannelFeed		= $youTubeFeedStart . "channel_id=" . $channel_id;
$playlist_id			= $params->get('playlist_id');
$youtubePlaylistFeed	= $youTubeFeedStart . "playlist_id=" . $playlist_id;
$numberOfFeedItems 		= $params->get('Number_of_feed_items');
$feedType				= $params->get('feed_type');
$Number_of_feed_items	= $params->get('Number_of_feed_items');
$container_class		= $params->get('container_class');
$row_class				= $params->get('row_class');
$column_class			= $params->get('column_class');
$order					= $params->get('order');

$moduleName 			= $module->module;

$schemaItemType = "VideoObject";

// Build the Feed
$feed = $youtubeChannelFeed;

if ($feedType == 0) {
	$feed = $youtubePlaylistFeed;
}

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

	

	$videoFeed	 	= json_decode(json_encode($xml), true); 
	$videoFeedEntry	= $videoFeed['entry'];
	if ($order == 1) {
		shuffle($videoFeedEntry);
	}
	?>
	
	<div class="<?php echo $moduleName . ' ' . $container_class ?>" itemscope itemtype="http://schema.org/<?php echo $schemaItemType; ?>">

	<?php
	$arrayChunks = array_chunk($videoFeedEntry, 4); 
	$i = 0;
	foreach($arrayChunks as $items) { 
	?>
		<div class="<?php echo $row_class; ?>">
			<?php
			foreach($items as $item) {
			if ($i == $numberOfForLoops) break; 
				$youtubeItemLink = substr($item['link']['@attributes']['href'], strpos($item['link']['@attributes']['href'], "?v=") + 3);		
				?>
				<div class="item<?php echo ' ' . $column_class; ?>">
					<div class="<?php echo $moduleName . "--Padding"; ?>">
						<div style="position: relative; padding-bottom: 56.25%; padding-top: 25px; height: 0;">
							<iframe 
								width="560" 
								height="315" 
								src="https://www.youtube.com/embed/<?php echo $youtubeItemLink; ?>"
								frameborder="0" 
								allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" 
								allowfullscreen
								style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"
								class="b-lazy"
								>
							</iframe>
							<div class="itemTitle--padder">
								<div class="itemTitle--image">
									<img src="../../../../images/joomla-london-brand-assets/videos-diagonal-background.svg" alt="" width="100%" height="auto">
								</div>
								<div class="itemTitle--holder">
									<div class="itemTitle">
										<?php echo $item['title']; ?>
									</div>					
								</div>								
							</div>							
						</div>
					</div>
				</div>
			<?php
			$i++;
			}
			echo "</div>";
	} ?>