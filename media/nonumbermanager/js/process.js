/**
 * Main JavaScript file
 *
 * @package         NoNumber Extension Manager
 * @version         5.2.1
 *
 * @author          Peter van Westen <peter@nonumber.nl>
 * @link            http://www.nonumber.nl
 * @copyright       Copyright © 2015 NoNumber All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

var NNEM_NNEM = 0;
var NNEM_IDS_FAILED = [];
var NNEM_MESSAGES = {'error': [], 'warning': []};
var NNEM_TASK = 'install';
var NNEM_INSTALL = 0;

(function($) {
	$(document).ready(function() {
		nnManagerProcess.resizeModal();
	});
	$(window.parent).resize(function() {
		nnManagerProcess.resizeModal();
	});

	if (typeof( window['nnManagerProcess'] ) != "undefined") {
		return;
	}

	nnManagerProcess = {

		process: function(task) {
			this.hide('title');
			this.show('processing', $('.titles'));

			NNEM_TASK = task;
			NNEM_INSTALL = (task != 'uninstall');

			var sb = window.parent.SqueezeBox;
			sb.overlay['removeEvent']('click', sb.bound.close);
			if (NNEM_REFRESH_ON_CLOSE || NNEM_IDS[0] == 'nonumberextensionmanager') {
				NNEM_NNEM = 1;
				sb.setOptions({
					onClose: function() {
						window.parent.location.href = window.parent.location;
					}
				});
			} else {
				sb.setOptions({
					onClose: function() {
						window.parent.nnManager.refreshData(1);
					}
				});
			}

			this.processNextStep(0);
		},

		processNextStep: function(step) {
			var id = NNEM_IDS[step];

			if (!id) {
				var sb = window.parent.SqueezeBox;
				this.hide('title');
				if (NNEM_IDS_FAILED.length) {
					this.showMessages('error', 'failed');
					this.showMessages('warning', 'failed');
					this.show('failed', $('.titles'));
					NNEM_IDS = NNEM_IDS_FAILED;
					NNEM_IDS_FAILED = [];
				} else {
					this.hide('processlist');
					this.showMessages('warning', 'done');
					this.show('done', $('.titles'));
					if (!NNEM_NNEM) {
						window.parent.nnManager.refreshData(1);
						sb.removeEvents();
					}
				}
				sb.overlay['addEvent']('click', sb.bound.close);
			} else {
				this.install(step)
			}
			this.resizeModal();
		},

		install: function(step) {
			var id = NNEM_IDS[step];

			this.hide('status', $('tr#row_' + id));
			this.show('processing_' + id);

			var url = 'index.php?option=com_nonumbermanager&view=process&tmpl=component&id=' + id;
			if (NNEM_INSTALL) {
				url += '&action=install';
				ext_url = $('#url_' + id).val() + '&action=' + NNEM_TASK + '&host=' + window.location.hostname;
				url += '&url=' + encodeURIComponent(ext_url);
			} else {
				url += '&action=uninstall';
			}
			nnScripts.loadajax(url,
				'nnManagerProcess.processResult( data.trim(), ' + step + ' )',
				'nnManagerProcess.processResult( data.trim(), ' + step + ' )',
				NNEM_TOKEN + '=1'
			);
		},

		processResult: function(data, step) {
			var id = NNEM_IDS[step];

			this.hide('status', $('tr#row_' + id));
			if (!data || ( data !== '1' && data.indexOf('<div class="alert alert-success"') == -1 )) {
				NNEM_IDS_FAILED.push(id);
				this.enqueueMessages('error', id, data);
				this.show('failed_' + id);
			} else {
				this.show('success_' + id);
			}
			this.enqueueMessages('warning', id, data);
			this.processNextStep(++step);
		},

		show: function(classes, parent) {
			if (!parent) {
				parent = $('div#nnem');
			} else {
				parent.addClass(classes.replace(',', ''));
			}
			classes = '.' + classes.replace(', ', ', .')
			parent.find(classes).removeClass('hide');
		},

		hide: function(classes, parent) {
			if (!parent) {
				parent = $('div#nnem');
			} else {
				parent.removeClass(classes.replace(',', ''));
			}
			classes = '.' + classes.replace(', ', ', .')
			parent.find(classes).addClass('hide');
		},

		showMessages: function(type, parent_class) {
			if (!NNEM_MESSAGES[type].length) {
				return;
			}

			$('.' + parent_class + ' .' + type + 's > div').html('<p class="alert-message">' + NNEM_MESSAGES[type].join('</p><p class="alert-message">') + '</p>');
			$('.' + parent_class + ' .' + type + 's').show();

			NNEM_MESSAGES[type] = [];
		},

		enqueueMessages: function(type, id, data) {
			var title = '<strong>' + $('#ext_name_' + id).html() + '</strong><br />';

			if (data.indexOf('</') == -1) {
				if (type == 'error') {
					NNEM_MESSAGES[type].push(title + data);
				}

				return;
			}

			var regex = new RegExp('<div class="alert '
				+ ( type == 'warning' ? '(?:alert-warning)?' : 'alert-' + type)
				+ '">[\\s\\S]*?<p class="alert-message">([\\s\\S]*?)<\\/p>', 'm');
			var match = data.match(regex);

			if (!match) {
				return;
			}

			var message = match[1];

			if (message.indexOf('JFolder: :delete') != -1) {
				return;
			}

			NNEM_MESSAGES[type].push(title + message);
		},

		resizeModal: function() {
			var orig_height = $('.sbox-content-iframe > iframe', window.parent.document).height();
			var max_height = $(window.parent).height() - 100;
			var new_height = $('#nnem').height() + 30;

			if (new_height < orig_height && new_height > orig_height - 20) {
				new_height = orig_height;
			}
			if (new_height > max_height) {
				new_height = max_height;
			}

			if (new_height == orig_height) {
				return;
			}

			window.parent.SqueezeBox.resize({x: 480, y: new_height});

			new_width = $('.sbox-content-iframe', window.parent.document).width();
			$('.sbox-content-iframe > iframe', window.parent.document).width(new_width).height(new_height);
		}
	}
})(jQuery);
