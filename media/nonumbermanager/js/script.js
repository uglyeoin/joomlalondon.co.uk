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

(function($) {
	if (typeof( window['nnManager'] ) != "undefined") {
		return;
	}

	nnem_has_pro_installed = false;
	nnem_show_key_invalid = true;

	nnManager = {
		refreshData: function(external) {
			nnManager.hide('loaded, data');
			nnManager.show('progress');

			$('div#nnem').find('tr').removeClass();
			$('div#nnem').find('td.ext_new').removeClass('disabled');

			var url = 'index.php?option=com_nonumbermanager&task=update&' + new Date().getTime();

			$.getJSON(url, function(data) {
				$.each(data, function(key, val) {
					nnManager.setDataByExtension(key, val);
				});

				if (external) {
					nnManager.hide('progress', $('.ext_types'));
					nnManager.show('loaded, no_external', $('.ext_types'));
					nnManager.refreshExternalData();

					return;
				}

				nnManager.hide('progress');
				nnManager.show('loaded, no_external');
			}).fail(function() {
				alert('Could not retrieve data.');

				nnManager.hide('progress');
				nnManager.show('loaded, no_external');
			});
		},

		refreshExternalData: function() {
			var url = 'download.nonumber.nl/extensions.json?j=3';
			if (NNEM_KEY) {
				url += '&k=' + NNEM_KEY;
			}

			nnScripts.loadajax(url, 'nnManager.setExternalData(data)', 'nnManager.setExternalData(0)', '', NNEM_TIMEOUT, 'json');
		},

		setExternalData: function(data) {
			if (!(data)) {
				alert(NNEM_FAIL);

				nnManager.hide('progress');
				nnManager.show('loaded, no_external');

				return;
			}

			var toolbar = $('div#toolbar');

			// reset stuff
			toolbar.removeClass('has_install').removeClass('has_reinstall').removeClass('has_update');

			$.each(data, function(key, val) {
				nnManager.setExternalDataByExtension(key, val);
			});

			if (nnem_has_pro_installed && nnem_show_key_invalid) {
				$('#nnkey_text_empty').hide();
				$('#nnkey_text_invalid').show();
				$('#nnkey .well').addClass('well-danger');
				$('#nnkey').show();
			}

			nnManager.hide('progress');
			nnManager.show('loaded');

			nnManager.updateCheckboxes();
		},

		setDataByExtension: function(extension, data) {
			if (!NNEM_IDS.indexOf(extension) < 0) {
				return;
			}

			var tr = $('tr#row_' + extension);

			if (!tr) {
				return;
			}

			nnManager.show('pro_not_installed', tr);

			if (!data || typeof(data['version']) == 'undefined' || !data['version']) {
				tr.find('span.current_version').text('');
				nnManager.hide('installed', tr);
				nnManager.show('not_installed', tr);

				return;
			}

			tr.find('span.current_version').text(data['version']);

			if (data['pro'] == 1) {
				nnManager.hide('pro_not_installed', tr)
				nnManager.show('pro_installed', tr);
				nnManager.show('uptodate', tr);
			} else {
				nnManager.show('free_installed', tr);
				nnManager.show('uptodate', tr);
			}

			if (data['missing'].length) {
				tr.find('.missing span').attr('data-content', data['missing']);
				nnManager.show('has_missing', tr);
			}

			nnManager.hide('not_installed', tr);
			nnManager.show('installed', tr);
		},

		setExternalDataByExtension: function(extension, data) {
			if (!NNEM_IDS.indexOf(extension) < 0) {
				return;
			}

			var div = $('div#nnem');
			var toolbar = $('div#toolbar');
			var tr = div.find('tr#row_' + extension);

			if (!tr) {
				return;
			}

			// reset stuff
			tr.find('td.ext_new').removeClass('disabled');
			tr.find('.changelog, .changelog > span').removeClass('disabled');

			if (!data) {
				return;
			}

			// Changelog
			if (typeof(data['changelog']) !== 'undefined' && data['changelog'] != '') {
				changelog = data['changelog'].replace('font-size:1.2em;', '');
				tr.find('.changelog a').attr('data-content', changelog);
				nnManager.show('changelog', tr);
			}

			// Install buttons
			if (typeof(data['version']) == 'undefined' || data['version'] == '') {
				return;
			}

			var v_new = String(data['version']).trim();

			if (!v_new || v_new == '0') {
				// no new version fond: show refresh button
				nnManager.show('no_external', tr);

				return;
			}

			var version_current = String(tr.find('.current_version').first().text()).trim();

			var pro_installed = tr.hasClass('pro_installed');
			if (pro_installed) {
				nnem_has_pro_installed = true;
			}

			var pro_access = (data['pro'] == 1);
			if (pro_access) {
				nnManager.show('pro_access', tr);
				$('#url_' + extension).val(data['downloadurl_pro']);
				nnem_show_key_invalid = false;
			} else {
				nnManager.show('pro_no_access', tr);
				$('#url_' + extension).val(data['downloadurl']);
			}

			var pro_available = (data['has_pro'] == 1);
			if (pro_available) {
				nnManager.show('pro_available', tr);
			} else {
				nnManager.show('pro_not_available', tr);
			}

			tr.find('.new_version').text(v_new);
			nnManager.show('changelog', tr);
			nnManager.hide('uptodate', tr);

			// No current version found
			if (!version_current || version_current == '0') {
				toolbar.addClass('has_install');
				nnManager.show('selectable', tr);
				nnManager.show('install', tr);

				return;
			}

			compare = nnScripts.compareVersions(version_current, v_new);

			// Current version is newer (dev version)
			if (compare == '>') {
				nnManager.show('downgrade', tr);
				nnManager.show('ext_install .downgrade .pro_access', tr);
				tr.find('.changelog, .changelog > span').addClass('disabled');
				if (pro_installed && pro_available && !pro_access) {
					nnManager.hide('pro_no_access', tr);
					nnManager.hide('ext_installed .pro_no_access', tr);
					nnManager.hide('ext_install .downgrade .pro_access', tr);

					nnManager.show('ext_install .downgrade .pro_no_access', tr);
				}
				return;
			}

			// Pro installed, but no access (Download Key invalid)
			if (pro_installed && pro_available && !pro_access) {
				if ($('#key_hidden').val()) {
					nnManager.show('pro_key_invalid', tr);
				}

				return;
			}

			// Current version is older or free installed and access to pro
			if (compare == '<' || (!pro_installed && pro_access)) {
				toolbar.addClass('has_update');
				nnManager.show('selectable', tr);
				nnManager.show('update', tr);

				return;
			}

			// Extension has missing parts
			if (tr.hasClass('has_missing')) {
				toolbar.addClass('has_install');
				nnManager.show('selectable', tr);

				return;
			}

			// All is uptodate
			tr.find('.changelog, .changelog > span').addClass('disabled');
			nnManager.show('uptodate', tr);
			nnManager.show('reinstall', tr);
			toolbar.addClass('has_reinstall');
		},

		updateCheckboxes: function() {
			var div = $('div#nnem');

			// hide select boxes
			nnManager.hide('select');

			// reset hidden checkboxes
			div.find('table tr.not_installed').each(function(i, tr) {
				if (tr.hasClass('xselectable')) {
					tr.addClass('selectable').removeClass('xselectable');
				}
			});

			// make hidden rows unselectable
			div.find('table.hide_not_installed tr.not_installed').each(function(i, tr) {
				if (tr.hasClass('selectable')) {
					tr.addClass('xselectable').removeClass('selectable');
				}
			});

			// show select boxes of selectable rows
			nnManager.show('selectable .select');
		},

		install: function(task, id) {
			var url = $('#url_' + id).val();
			nnManager.openModal(task, [id], [url]);
		},

		installMultiple: function(task) {
			var ids = [];
			var urls = [];

			switch (task) {
				case 'reinstallall':
					type = 'reinstall';
					msg = NNEM_NOUPDATE;
					clss = 'reinstall';
					break;
				case 'updateall':
					type = 'update';
					msg = NNEM_NOUPDATE;
					clss = 'selectable.update';
					break;
				default:
					type = 'install';
					msg = NNEM_NONESELECTED;
					clss = 'selectable';
					break;
			}

			$('div#nnem tr.' + clss).each(function() {
				var tr = $(this);
				var el = tr.find('td.ext_checkbox input');
				var id = el.val();
				if (id) {
					var pass = 0;
					switch (task) {
						case 'reinstallall':
						case 'updateall':
							pass = true;
							break;
						default:
							pass = el.is(':checked');
							break;
					}

					if (pass) {
						var url = $('#url_' + id).val();
						ids.push(id);
						urls.push(url);
					}
				}
			});

			if (!ids.length) {
				alert(msg);
			} else {
				nnManager.openModal(type, ids, urls);
			}
		},

		openModal: function(task, ids, urls, refresh_on_close) {
			var url_ids = [];
			for (var i = 0; i < ids.length; i++) {
				url_ids.push('ids[]=' + escape(ids[i]))
			}

			url_ids = url_ids.join('&');

			var url_urls = [];
			for (var j = 0; j < urls.length; j++) {
				url = urls[j].replace('http://', '');
				url_urls.push('urls[]=' + escape(url));
			}
			url_urls = url_urls.join('&');

			var url = 'index.php?option=com_nonumbermanager&view=process&tmpl=component&task=' + task + '&' + url_ids + '&' + url_urls;

			if (refresh_on_close) {
				url += '&refresh_on_close=1';
			}

			SqueezeBox.open(url, {handler: 'iframe', size: {x: 480, y: 110}, classWindow: 'nnem_modal'});
		},

		show: function(classes, parent) {
			this.toggle(classes, parent, 1);
		},

		hide: function(classes, parent) {
			this.toggle(classes, parent, 0);
		},

		toggle: function(classes, parent, show) {
			var classes = classes.split(',');
			$(classes).each(function(i, el) {
				c = classes[i].trim();
				if (!parent) {
					parent = $('div#nnem');
				} else {
					if (c != 'progress' && c != 'loaded') {
						if (show) {
							parent.addClass(c);
						} else {
							parent.removeClass(c);
						}
					}
				}
				if (show) {
					parent.find('.' + c).removeClass('hide');
				} else {
					parent.find('.' + c).addClass('hide');
				}
			});
		}
	}
})(jQuery);

function nnem_function(task, id) {
	if (!task) {
		return;
	}

	switch (task) {
		case 'refresh':
			nnManager.refreshData(1);
			break;
		case 'reinstallall':
		case 'updateall':
		case 'installselected':
			nnManager.installMultiple(task);
			break;
		case 'install':
		case 'update':
		case 'reinstall':
		case 'downgrade':
		case 'uninstall':
			nnManager.install(task, id);
			break;
	}
}
