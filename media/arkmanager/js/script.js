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

(function($) {
	if (typeof( window['arkManager'] ) == "undefined") {
		arkManager = {
			refreshData: function(external) {
				arkManager.hide('loaded, data');
				arkManager.show('progress');

				$('div#arkem').find('tr').removeClass();
				$('div#arkem').find('td.ext_new').removeClass('disabled');

				var url = 'index.php?option=com_arkmanager&task=update';
				arkScripts.loadajax(url, 'arkManager.setData( data, ' + external + ' )', 'arkManager.setData( 0 )', AEM_TOKEN + '=1', '', 'xml');
			},

			setData: function(data, external) {
				var xml = arkScripts.getObjectFromXML(data);
				
			if (!xml) {
					return;
				}

				for (var i = 0; i < AEM_IDS.length; i++) {
					var extension = AEM_IDS[i];
					var dat = 0;
					if (typeof(xml[extension]) !== 'undefined') {
						dat = xml[extension];
					}

					tr = $('tr#row_' + extension);

					// Versions
					if (tr) {
						arkManager.show('pro_not_installed', tr);
						if (dat && typeof(dat['version']) !== 'undefined' && dat['version']) {
							tr.find('span.current_version').text(dat['version']);
							if (dat['pro'] == 1) {
								arkManager.hide('pro_not_installed', tr)
								arkManager.show('pro_installed', tr);
							} else if (dat['old'] == 1) {
								arkManager.show('old_installed', tr);
							} else {
								arkManager.show('free_installed', tr);
							}
							if (dat['missing']) {
								tr.find('.missing span').attr('data-content', dat['missing']);
								arkManager.show('has_missing', tr);
							}
							arkManager.show('installed', tr);
						} else {
							tr.find('span.current_version').text('');
							arkManager.show('not_installed', tr);
						}
					}
				}
				if (external) {
					arkManager.hide('progress', $('.ext_types'));
					arkManager.show('loaded, no_external', $('.ext_types'));
					arkManager.refreshExternalData();
				} else {
					arkManager.hide('progress');
					arkManager.show('loaded, no_external');
				}
			},

			refreshExternalData: function() {
			
				var url = 'arkextensions.com/index.php?option=com_ajax&plugin=arkversions&format=raw'
				if (AEM_KEY) {
					url += '&k=' + AEM_KEY;
				}
				arkScripts.loadajax(url, 'arkManager.setExternalData(data)', 'arkManager.setExternalData(0)', '', AEM_TIMEOUT, 'xml');
			},

			setExternalData: function(data) {
				var xml = arkScripts.getObjectFromXML(data);
				if (!xml) {
					alert(AEM_FAIL);

					arkManager.hide('progress');
					arkManager.show('loaded, no_external');
					return;
				}

				
				
				
				div = $('div#arkem');
				toolbar = $('div#toolbar');

				// reset stuff
				toolbar.removeClass('has_install').removeClass('has_update');

				for (var i = 0; i < AEM_IDS.length; i++) {
					var extension = AEM_IDS[i];
					var dat = 0;

					if (typeof(xml[extension]) !== 'undefined') {
						dat = xml[extension];
					}

					tr = div.find('tr#row_' + extension);

					// reset stuff
					tr.find('td.ext_new').removeClass('disabled');
					tr.find('.changelog, .changelog > span').removeClass('disabled');

					if (!dat) {
						arkManager.show('uptodate', tr);
					} else {
						// Changelog
						if (typeof(dat['changelog']) !== 'undefined' && dat['changelog'] != '') {
							changelog = dat['changelog'].replace('font-size:1.2em;', '');
							tr.find('.changelog a').attr('data-content', changelog);
							arkManager.show('changelog', tr);
						}

						// Install buttons
						if (typeof(dat['version']) !== 'undefined' && dat['version'] != '') {
							v_new = String(dat['version']).trim();

							if (!v_new || v_new == '0') {
								// no new version fond: show refresh button
								arkManager.show('no_external', tr);
							} else {
								v_old = String(tr.find('.current_version').first().text()).trim();
								is_old = ( tr.id == 'row_nonumberextensionmanager' ) ? 0 : tr.hasClass('old_installed');

								pro_installed = tr.hasClass('pro_installed');

								pro_access = (dat['pro'] == 1);
								if (pro_access) {
									arkManager.show('pro_access', tr);
									$('#url_' + extension).val(dat['downloadurl_pro']+'&token=' +AEM_KEY);
								} else {
									arkManager.show('pro_no_access', tr);
									$('#url_' + extension).val(dat['downloadurl']+'&token=' +AEM_KEY);
								}

								pro_available = (dat['has_pro'] == 1);
								if (pro_available) {
									arkManager.show('pro_available', tr);
								} else {
									arkManager.show('pro_not_available', tr);
								}
								
													
								tr.find('.new_version').text(v_new);
								arkManager.show('changelog', tr);

								if (!v_old || v_old == '0') {
									toolbar.addClass('has_install');
									arkManager.show('selectable', tr);
									if (dat['downloadurl'] || pro_access)
										arkManager.show('install', tr);
								} else if (is_old && pro_available && !pro_access) {
									arkManager.show('old', tr);
								} else if (tr.hasClass('has_missing')) {
									toolbar.addClass('has_install');
									arkManager.show('selectable', tr);
                                    if (dat['downloadurl'] || pro_access)
									    arkManager.show('install', tr);
								} else if (pro_available && pro_installed && !pro_access) {
									arkManager.show('pro_no_access', tr);
								} else {
									compare = arkScripts.compareVersions(v_old, v_new);
									if (compare == '<' || (!pro_installed && pro_access)) {
										toolbar.addClass('has_update');
										arkManager.show('selectable', tr);
                                        if (dat['downloadurl'] || pro_access)
										    arkManager.show('update', tr);
									} else if (compare == '>') {
										arkManager.show('downgrade', tr);
										tr.find('td.ext_new').addClass('disabled');
									} else {
										tr.find('.changelog, .changelog > span').addClass('disabled');
										arkManager.show('uptodate', tr);
                                        if (dat['downloadurl'] || pro_access)
										    arkManager.show('reinstall', tr);
									}
								}
							}
						}
					}
				}

				arkManager.hide('progress');
				arkManager.show('loaded');

				arkManager.updateCheckboxes();

				// unlock height of table rows
				//div.find('table td.checkbox').css('height', '');

				// unlock width of table columns
				//div.find('table th').css('min-width', '');
			},

			updateCheckboxes: function() {
				// hide select boxes
				arkManager.hide('select');

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
				arkManager.show('selectable .select');
			},

			install: function(task, id) {
				var url = $('#url_' + id).val();
				arkManager.openModal(task, [id], [url]);
			},

			installMultiple: function(task) {
				var ids = [];
				var urls = [];

				switch (task) {
					case 'updateall':
						type = 'update';
						msg = AEM_NOUPDATE;
						break;
					default:
						type = 'install';
						msg = AEM_NONESELECTED;
						break;
				}

				$('div#arkem tr.selectable').each(function() {
					tr = $(this);
					var el = tr.find('td.ext_checkbox input');
					id = el.val();
					if (id) {
						var pass = 0;
						switch (task) {
							case 'updateall':
								pass = tr.hasClass('update');
								break;
							default:
								pass = el.is(':checked');
								break;
						}

						if (pass) {
							url = $('#url_' + id).val();
							ids.push(id);
							urls.push(url);
						}
					}
				});

				if (!ids.length) {
					alert(msg);
				} else {
					arkManager.openModal(type, ids, urls);
				}
			},
			openModal: function(task, ids, urls) {
				a = [];
				for (var i = 0; i < ids.length; i++) {
					a.push('ids[]=' + escape(ids[i]))
				}

				width = 480;
				height = 58 + (a.length * 37);
				min = 140;
				max = window.getSize().y - 60;
				if (height > max) {
					height = max;
					width += 16;
				}
				if (height < min) {
					height = min;
				}

				a = a.join('&');

				b = [];
				for (var j = 0; j < urls.length; j++) {
					url = urls[j].replace('http://', '');
					b.push('urls[]=' + escape(url));
				}
				b = b.join('&');

				url = 'index.php?option=com_arkmanager&view=process&tmpl=component&task=' + task + '&' + a + '&' + b;
				SqueezeBox.open(url, {handler: 'iframe', size: {x: width, y: height}, classWindow: 'arkem_modal'});
			},

			show: function(classes, parent) {
				this.toggle(classes, parent, 1);
			},

			hide: function(classes, parent) {
				this.toggle(classes, parent, 0);
			},

			toggle: function(classes, parent, show) {
				classes = classes.split(',');
				$(classes).each(function(i, el) {
					c = classes[i].trim();
					if (!parent) {
						parent = $('div#arkem');
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
	}
})(jQuery);

function arkem_function(task, id) {
	if (!task) {
		return;
	}
	switch (task) {
		case 'refresh':
			arkManager.refreshData(1);
			break;
		case 'updateall':
		case 'installselected':
			arkManager.installMultiple(task);
			break;
		case 'install':
		case 'update':
		case 'reinstall':
		case 'downgrade':
		case 'uninstall':
			arkManager.install(task, id);
			break;
	}
}