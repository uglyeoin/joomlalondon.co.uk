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
    else {
        $numberOfForLoops = $numberOfFeedItems;
    }

// Review information in a loop
?>

<div class="<?php echo $module->module ?>" itemscope itemtype="http://schema.org/<?php echo $schemaItemType; echo $container_class?>">

<?php
for($i = 0; $i < $numberOfForLoops; $i++)
{

	$link 						=	$xml->entry->link[0]['href'];
	$youtubeItemTitle			= 	$xml->entry->title;
	$youtubeItemLink			=  	substr($link, strpos($link, "?v=") + 3);
	$youtubeItemDate			=  	date('l, d m Y' , strtotime($xml->entry->published));




	// Cut words if there is a word count selected.  Add an ellipses.  Add a link to see full review.
	/*
	if (str_word_count($comments, 0) > $maxWords) {
		$words = str_word_count($comments, 2);
		$position = array_keys($words);
		$comments = substr($comments, 0, $position[$maxWords])
			. "<br>"
			. '&hellip;'
			.  '<a href="' . itemUrl . '" class="feed_item_url" target="_blank"></a>';
	}
	echo $comments;
	*/

?>

	<div class="itemContainer<?php echo ' $row_class';?>">
		<div class="Item<?php echo ' $column_class';?>">
			<iframe 
				width="560" 
				height="315" 
				src="https://www.youtube.com/embed/<?php echo $youtubeItemLink;?>"
				frameborder="0" 
				allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" 
				allowfullscreen>
			</iframe>
		</div>
		<div class="ItemTitle">
			<?php echo $youtubeItemTitle; ?>
		</div>
	</div>

<?php ;} ?>
</div>


<?php
	/* DEBUGING */
	$user = JFactory::getUser();
	$isroot = $user->authorise('core.admin');
	if($isroot) {
		highlight_string("<?php\n\$xml->entry =\n" . var_export($xml->entry, true) . ";\n?>");
	}
?>