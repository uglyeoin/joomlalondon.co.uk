/**
 * MultiSelect JavaScript file
 *
 * @package         NoNumber Framework
 * @version         14.6.5
 *
 * @author          Peter van Westen <peter@nonumber.nl>
 * @link            http://www.nonumber.nl
 * @copyright       Copyright © 2014 NoNumber All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

(function($) {
	$(document).ready(function() {
		$('.ark_multiselect').each(function() {
			var controls = $(this).find('div.ark_multiselect-controls');
			var list = $(this).find('ul.ark_multiselect-ul');
			var menu = $(this).find('div.ark_multiselect-menu-block').html();
			var maxheight = list.css('max-height');

			list.find('li').each(function() {
				$li = $(this);
				$div = $li.find('div.ark_multiselect-item:first');

				// Add icons
				$li.prepend('<span class="pull-left icon-"></span>');

				// Append clearfix
				$div.after('<div class="clearfix"></div>');

				if ($li.find('ul.ark_multiselect-sub').length) {
					// Add classes to Expand/Collapse icons
					$li.find('span.icon-').addClass('ark_multiselect-toggle icon-minus');

					// Append drop down menu in nodes
					$div.find('label:first').after(menu);

					if (!$li.find('ul.ark_multiselect-sub ul.ark_multiselect-sub').length) {
						$li.find('div.ark_multiselect-menu-expand').remove();
					}
				}
			});

			// Takes care of the Expand/Collapse of a node
			list.find('span.ark_multiselect-toggle').click(function() {
				$icon = $(this);

				// Take care of parent UL
				if ($icon.parent().find('ul.ark_multiselect-sub').is(':visible')) {
					$icon.removeClass('icon-minus').addClass('icon-plus');
					$icon.parent().find('ul.ark_multiselect-sub').hide();
					$icon.parent().find('ul.ark_multiselect-sub span.ark_multiselect-toggle').removeClass('icon-minus').addClass('icon-plus');
				} else {
					$icon.removeClass('icon-plus').addClass('icon-minus');
					$icon.parent().find('ul.ark_multiselect-sub').show();
					$icon.parent().find('ul.ark_multiselect-sub span.ark_multiselect-toggle').removeClass('icon-plus').addClass('icon-minus');
				}
			});

			// Takes care of the filtering
			controls.find('input.ark_multiselect-filter').keyup(function() {
				$text = $(this).val().toLowerCase();
				list.find('li').each(function() {
					$li = $(this);
					if ($li.text().toLowerCase().indexOf($text) == -1) {
						$li.hide();
					} else {
						$li.show();
					}
				});
			});

			// Checks all checkboxes in the list
			controls.find('a.ark_multiselect-checkall').click(function() {
				list.find('input').prop('checked', true);
			});

			// Unchecks all checkboxes in the list
			controls.find('a.ark_multiselect-uncheckall').click(function() {
				list.find('input').prop('checked', false);
			});

			// Toggles all checkboxes in the list
			controls.find('a.ark_multiselect-toggleall').click(function() {
				list.find('input').each(function() {
					$input = $(this);
					if ($input.prop('checked')) {
						$input.prop('checked', false);
					} else {
						$input.prop('checked', true);
					}
				});
			});

			// Expands all sub-items in the list
			controls.find('a.ark_multiselect-expandall').click(function() {
				list.find('ul.ark_multiselect-sub').show();
				list.find('span.ark_multiselect-toggle').removeClass('icon-plus').addClass('icon-minus');
			});

			// Hides all sub-items in the list
			controls.find('a.ark_multiselect-collapseall').click(function() {
				list.find('ul.ark_multiselect-sub').hide();
				list.find('span.ark_multiselect-toggle').removeClass('icon-minus').addClass('icon-plus');
			});

			// Shows all selected items in the list
			controls.find('a.ark_multiselect-showall').click(function() {
				list.find('li').show();
			});

			// Shows all selected items in the list
			controls.find('a.ark_multiselect-showselected').click(function() {
				list.find('li').each(function() {
					$li = $(this);
					$hide = 1;
					$li.find('input').each(function() {
						$input = $(this);
						if ($input.prop('checked')) {
							$hide = 0;
							return;
						}
					});
					if ($hide) {
						$li.hide();
					} else {
						$li.show();
					}
				});
			});

			// Maximizes the list
			controls.find('a.ark_multiselect-maximize').click(function() {
				list.css('max-height', '');
				controls.find('a.ark_multiselect-maximize').hide();
				controls.find('a.ark_multiselect-minimize').show();
			});

			// Minimizes the list
			controls.find('a.ark_multiselect-minimize').click(function() {
				list.css('max-height', maxheight);
				controls.find('a.ark_multiselect-minimize').hide();
				controls.find('a.ark_multiselect-maximize').show();
			});

		});

		// Take care of children check/uncheck all
		$('div.ark_multiselect a.checkall').click(function() {
			$(this).parent().parent().parent().parent().parent().parent().find('ul.ark_multiselect-sub input').prop('checked', true);
		});
		$('div.ark_multiselect a.uncheckall').click(function() {
			$(this).parent().parent().parent().parent().parent().parent().find('ul.ark_multiselect-sub input').prop('checked', false);
		});

		// Take care of children toggle all
		$('div.ark_multiselect a.expandall').click(function() {
			$parent = $(this).parent().parent().parent().parent().parent().parent().parent();
			$parent.find('ul.ark_multiselect-sub').show();
			$parent.find('ul.ark_multiselect-sub span.ark_multiselect-toggle').removeClass('icon-plus').addClass('icon-minus');
			;
		});
		$('div.ark_multiselect a.collapseall').click(function() {
			$parent = $(this).parent().parent().parent().parent().parent().parent().parent();
			$parent.find('li ul.ark_multiselect-sub').hide();
			$parent.find('li span.ark_multiselect-toggle').removeClass('icon-minus').addClass('icon-plus');
			;
		});
		$('div.ark_multiselect-item.hidechildren').click(function() {
			$parent = $(this).parent();

			$(this).find('input').each(function() {
				$sub = $parent.find('ul.ark_multiselect-sub').first();
				$input = $(this);
				if ($input.prop('checked')) {
					$parent.find('span.ark_multiselect-toggle, div.ark_multiselect-menu').css('visibility', 'hidden');
					if (!$sub.parent().hasClass('hidelist')) {
						$sub.wrap('<div style="display:none;" class="hidelist"></div>');
					}
				} else {
					$parent.find('span.ark_multiselect-toggle, div.ark_multiselect-menu').css('visibility', 'visible');
					if ($sub.parent().hasClass('hidelist')) {
						$sub.unwrap();
					}
				}
			});
			;
		});

	});
})(jQuery);
