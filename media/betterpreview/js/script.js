/**
 * @package         Better Preview
 * @version         6.2.2PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2019 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

(function($) {
	"use strict";

	$(document).ready(function() {
		$('.betterpreview-dropdown .dropdown-toggle').hover(function() {
			var el   = $(this).parent();
			var menu = el.find('.dropdown-menu');

			menu.stop(true, true).show();
			el.addClass('open');

			var hide = function() {
				menu.stop(true, true).hide();
				el.removeClass('open');
			};

			$('html').click(function() {
				hide();
			});
			menu.hover(function() {
			}, function() {
				hide();
			});
			$('#menu').hover(function() {
				hide();
			});
		});

		$('body').append('<div id="betterpreview_urlinfo" class="modal hide fade">'
			+ '<div class="modal-header">'
			+ '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>'
			+ '<h3 class="modal-title"></h3>'
			+ '</div>'
			+ '<div class="modal-body"></div>'
			+ '</div>');
	});
})(jQuery);

function betterpreview_show_info(title, type, url, nonsef, error) {
	(function($) {
		"use strict";

		// Type
		if (type) {
			title += ' <span class="label">' + type + '</span>'
		}

		$('#betterpreview_urlinfo h3.modal-title').html(title);

		var content = '';

		// URL
		content += '<h4>' + betterpreview_texts.url + ':</h4>';
		if (error) {
			content += '<span class="label label-important">' + error + '</span>'
		} else {
			content += '<a href="' + url + '" target="_blank"><span class="pull-right icon-url"></span></a>' +
				'<pre>' + url + '</pre>';
		}

		// Non-SEF URL
		content += '<h4>' + betterpreview_texts.nonsef + ':</h4>';
		if (!error) {
			content += '<a href="' + nonsef + '" target="_blank"><span class="pull-right icon-url"></span></a>';
		}
		content += '<pre>' + nonsef + '</pre>';

		content = '<div class="container-popup">' + content + '</div>';

		$('#betterpreview_urlinfo div.modal-body').html(content);
		$('#betterpreview_urlinfo').modal('show')
	})(jQuery);
}
