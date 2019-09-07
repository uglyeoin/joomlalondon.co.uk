<script>
gantry-assets://custom/assets/js/typed.min.js

jQuery(function(){
	jQuery("#typed").typed({
		strings: ["Hobbyist?", "Business Owner?", "Designer?", "Developer?"],
		typeSpeed: 30,
		backDelay: 500,
		loop: false,
		contentType: 'html', // or text
		// defaults to false for infinite loop
		loopCount: false,
showCursor: false,
		callback: function() {
			jQuery(function() {
				var hideme = jQuery(".type-wrap");
				var hideme2 = jQuery("#ru");			
				my_timer = setTimeout(function () {
					hideme.hide();
					hideme2.hide();
				}, 500);
			});
			jQuery(function() {
				var showme = jQuery("#dazzle");
				var showme2 = jQuery("#iam");
				my_timer = setTimeout(function () {
					showme.show();
					showme2.show();
				}, 501);							
			});
		}
	});
});