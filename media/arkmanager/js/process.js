/**
 * Main JavaScript file
 *
 * @package         NoNumber Extension Manager
 * @version         4.3.1
 *
 * @author          Peter van Westen <peter@nonumber.nl>
 * @link            http://www.nonumber.nl
 * @copyright       Copyright © 2014 NoNumber All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

var AEM_NNEM = 0;
var AEM_IDS_FAILED = [];
var AEM_TASK = 'install';
var AEM_INSTALL = 0;

(function($) {
	if (typeof( window['arkManagerProcess'] ) == "undefined") {
		arkManagerProcess = {

			process: function(task) {
				this.hide('title');
				this.show('processing', $('.titles'));

				AEM_TASK = task;
				AEM_INSTALL = (task != 'uninstall');

				var sb = window.parent.SqueezeBox;
				sb.overlay['removeEvent']('click', sb.bound.close);
				if (AEM_IDS[0] == 'arkmanager') {
					AEM_NNEM = 1;
					sb.setOptions({onClose: function() { window.parent.location.href = window.parent.location; }});
				} else {
					sb.setOptions({onClose: function() { window.parent.arkManager.refreshData(1); }});
				}

				this.processNextStep(0);
			},

			processNextStep: function(step) {
				var id = AEM_IDS[step];

				if (!id) {
					var sb = window.parent.SqueezeBox;
					this.hide('title');
					if (AEM_IDS_FAILED.length) {
						this.show('failed', $('.titles'));
						AEM_IDS = AEM_IDS_FAILED;
						AEM_IDS_FAILED = [];
					} else {
						this.hide('processlist');
						this.show('done', $('.titles'));
						if (!AEM_NNEM) {
							window.parent.arkManager.refreshData(1);
							sb.removeEvents();
						}
					}
					sb.overlay['addEvent']('click', sb.bound.close);
				} else {
					this.install(step)
				}
			},

			install: function(step) {
				var id = AEM_IDS[step];

				this.hide('status', $('tr#row_' + id));
				this.show('processing_' + id);

				var url = 'index.php?option=com_arkmanager&view=process&tmpl=component&id=' + id;
				if (AEM_INSTALL) {
					url += '&action=install';
					ext_url = $('#url_' + id).val() + '&action=' + AEM_TASK
					url += '&url=' + escape(ext_url);
				} else {
					url += '&action=uninstall';
				}
				arkScripts.loadajax(url, 'arkManagerProcess.processResult( data.trim(), ' + step + ' )', 'arkManagerProcess.processResult( 0, ' + step + ' )', AEM_TOKEN + '=1');
			},

			processResult: function(data, step) {
				var id = AEM_IDS[step];

				this.hide('status', $('tr#row_' + id));
				if (!data || ( data !== '1' && data.indexOf('<div class="alert alert-success"') == -1 )) {
					AEM_IDS_FAILED.push(id);
					this.show('failed_' + id);
				} else {
					this.show('success_' + id);
				}
				this.processNextStep(++step);
			},

			show: function(classes, parent) {
				if (!parent) {
					parent = $('div#arkem');
				} else {
					parent.addClass(classes.replace(',', ''));
				}
				classes = '.' + classes.replace(', ', ', .')
				parent.find(classes).removeClass('hide');
			},

			hide: function(classes, parent) {
				if (!parent) {
					parent = $('div#arkem');
				} else {
					parent.removeClass(classes.replace(',', ''));
				}
				classes = '.' + classes.replace(', ', ', .')
				parent.find(classes).addClass('hide');
			}
		}
	}
})(jQuery);
