<?php
	class mod_trust_a_trader {
		public function starRatingImage($rating){
			// Build the correct output so we can produce the star rating image
			$round = number_format(round($rating, 1),1);
			$imageNumber = str_replace('.', '', $round);
			$image = "star-ratings_0" . $imageNumber . ".png";
		?>
			<div class="tt-stars">
				<img src="<?php echo JURI::root() . "media/mod_kou_youtube_rss_playlist_or_channel/images/" . $image; ?>" alt="<?php echo $rating . " star rating"?>" />
			</div>
		<?php
			}
	}
?>