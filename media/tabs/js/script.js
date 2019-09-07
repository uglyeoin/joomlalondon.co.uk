/**
 * @package         Tabs
 * @version         7.5.9PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2019 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

var RegularLabsTabs = null;

(function($) {
	"use strict";

	RegularLabsTabs = {
		options    : {},
		timers     : [],
		stop_timers: [],
		scroll_to  : null,
		scrolling  : false,

		init: function(options) {
			var self = this;

			options      = options ? options : this.getOptions();
			this.options = options;

			try {
				this.hash_id = decodeURIComponent(window.location.hash.replace('#', ''));
				// Ignore the url hash if it contains weird characters
				if (this.hash_id.indexOf('/') > -1 || this.hash_id.indexOf('/') > -1) {
					this.hash_id = '';
				}
			} catch (err) {
				this.hash_id = '';
			}

			this.current_url = window.location.href;
			if (this.current_url.indexOf('#') > -1) {
				this.current_url = this.current_url.substr(0, this.current_url.indexOf('#'));
			}
			this.current_path = this.current_url.replace(/^.*\/\/.*?\//, '');

			// Remove the transition durations off to make initial setting of active tabs as fast as possible
			$('.rl_tabs').removeClass('has_effects');

			if (options.use_cookies) {
				self.showByCookies();
			}

			this.initScrollTracking();

			this.showByURL();

			this.showByHash();

			this.initEqualHeights();

			setTimeout((function() {
				self.initActiveClasses();

				self.initResponsiveScrolling();

				self.initClickMode();

				self.initHoverMode();

				if (options.use_cookies || options.set_cookies) {
					self.initCookieHandling();
				}

				if (options.use_hash) {
					self.initHashHandling();
				}

				self.initHashLinkList();

				if (options.reload_iframes) {
					self.initIframeReloading();
				}

				self.initSlideshows();

				// Add the transition durations
				$('.rl_tabs').addClass('has_effects');
			}), 1000);

		},

		show: function(id, scroll, openparents, slideshow) {
			if (openparents) {
				this.openParents(id);
				return;
			}

			var self = this;
			var $el  = this.getElement(id);

			if (!$el.length) {
				return;
			}

			if (scroll && !$el.hasClass('in')) {
				$el.one('shown.bs.tab', function() {
					$('html,body').animate({scrollTop: self.getScrollToElement($el).offset().top});
				});
			}

			if (this.scroll_to) {

				this.setScrollOnLoad($el);
			}

			$el.tab('show');

			$el.closest('ul.nav-tabs').find('.rl_tabs-toggle').attr('aria-selected', false).attr('aria-expanded', false);
			$el.attr('aria-selected', true).attr('aria-expanded', true);

			$el.closest('div.rl_tabs').find('.tab-content').first().children().attr('aria-hidden', true);
			$('div#' + id).attr('aria-hidden', false);

			this.updateActiveClassesOnTabLinks($el);

			// trigger resize event to make certain scripts (like galleries) work
			window.dispatchEvent(new Event('resize'));

			if (!slideshow) {
				// For some reason Chrome 67 throws an error when not using a small delay
				setTimeout(function() {
					$el[0].focus();
				}, 10);
			}
		},

		setScrollOnLoad: function($el) {

			var self = this;

			// If tab is already open, do scroll immediately
			if ($el.parent().hasClass('in') || $el.parent().hasClass('active')) {
				self.scrollOnLoad();
				return;
			}

			// If tab is not open yet, do scroll when opened
			$el.one('shown.bs.tab', function() {
				self.scrollOnLoad();
			});
		},

		scrollOnLoad: function() {
			var self = this;

			if (this.scrolling) {
				setTimeout(function() {
					self.scrollOnLoad();
				}, 100);

				return;
			}

			clearTimeout(self.timers['scroll']);

			self.timers['scroll'] = setTimeout(function() {
				if (!self.scroll_to) {
					return;
				}

				$('html,body').animate({scrollTop: self.scroll_to.offset().top});
				self.scroll_to = null;
			}, 100);
		},

		fixEqualContentHeights: function() {
			$('.rl_tabs.bottom').each(function() {
				$(this).find('.tab-content').first().after($(this).find('.nav-tabs').first());
			});

			$('.rl_tabs.left').each(function() {
				if ($(window).width() <= 767 && $(this).hasClass('rl_tabs-responsive')) {
					$(this).find('.tab-content').first()
						.css('margin-left', 0)
						.css('min-height', 0);

					return;
				}

				$(this).find('.tab-content').first()
					.css('margin-left', $(this).find('.nav-tabs').first().width())
					.css('min-height', $(this).find('.nav-tabs').first().height());
			});

			$('.rl_tabs.right').each(function() {
				if ($(window).width() <= 767 && $(this).hasClass('rl_tabs-responsive')) {
					$(this).find('.tab-content').first()
						.css('margin-right', 0)
						.css('min-height', 0);

					return;
				}

				$(this).find('.tab-content').first()
					.css('margin-right', $(this).find('.nav-tabs').first().width())
					.css('min-height', $(this).find('.nav-tabs').first().height());
			});
		},

		getElement: function(id) {
			return this.getTabElement(id);
		},

		getTabElement: function(id) {
			return $('a.rl_tabs-toggle[data-id="' + id + '"]');
		},

		getSliderElement: function(id) {
			return $('#' + id + '.rl_sliders-body');
		},

		showByCookies: function() {
			var cookies = $.cookie(this.options.cookie_name);
			if (!cookies) {
				return;
			}

			cookies = cookies.split('___');
			for (var i = 0; i < cookies.length; i++) {
				var keyval = cookies[i].split('=');
				if (keyval.length < 2) {
					continue;
				}

				var key = keyval.shift();
				if (key.substr(0, 11) != 'set-rl_tabs') {
					continue;
				}

				this.openParents(decodeURIComponent(keyval.join('=')));
			}
		},

		initScrollTracking: function() {
			var self = this;

			self.scrolling = true;

			self.timers['scrolling'] = setTimeout((function() {
				self.scrolling = false;
			}), 250);

			var scroll_function_orig = window.onscroll;

			window.onscroll = (function() {
				self.scrolling = true;

				clearTimeout(self.timers['scrolling']);

				self.timers['scrolling'] = setTimeout((function() {
					self.scrolling = false;
				}), 250);

				if (scroll_function_orig) {
					scroll_function_orig();
				}
			});
		},

		showByURL: function() {
			var id = this.getUrlVar();

			if (id == '') {
				return;
			}

			this.showByID(id, this.options.urlscroll);
		},

		showByHash: function() {
			if (this.hash_id == '') {
				return;
			}

			var id = this.hash_id;

			if (id == '' || id.indexOf("&") > -1 || id.indexOf("=") > -1) {
				return;
			}

			// check if element is a slider -> leave to Sliders
			if ($('a#rl_sliders-scrollto_' + id).length) {
				return;
			}

			// check if element is not a tab
			if (!$('a.rl_tabs-toggle[data-id="' + id + '"]').length) {
				this.showByHashAnchor(id);

				return;
			}

			// hash is a tab
			if (!this.options.use_hash) {
				return;
			}

			if (!this.options.urlscroll) {
				// Prevent scrolling to anchor
				$('html,body').animate({scrollTop: 0});
			}

			this.showByID(id, this.options.urlscroll);
		},

		showByHashAnchor: function(id) {
			if (id == '') {
				return;
			}

			var $anchor = $('[id="' + id + '"],a[name="' + id + '"],a#anchor-' + id);

			if (!$anchor.length) {
				return;
			}

			$anchor = $anchor.first();

			// Check if anchor has a parent tab
			if (!$anchor.closest('.rl_tabs').length) {
				return;
			}

			var $tab = $anchor.closest('.tab-pane').first();

			this.setScrollToElement($anchor);

			this.openParents($tab.attr('id'));
		},

		showByID: function(id, scroll) {
			var $el = $('a.rl_tabs-toggle[data-id="' + id + '"]');

			if (!$el.length) {
				return;
			}

			if (scroll) {
				this.setScrollToElement(this.getScrollToElement($el));
			}

			this.openParents(id);
		},

		getScrollToElement: function($el) {
			var $scroll_to = $el.closest('ul:not(.dropdown-menu)').parent().find('.rl_tabs-scroll').first();

			if ($(window).width() <= 767) {
				$scroll_to = $('a#anchor-' + $el.attr('data-id')).first();
			}

			if (!$scroll_to.length) {
				return null;
			}

			return $scroll_to;
		},

		setScrollToElement: function($el) {
			if (!$el.length) {
				return;
			}

			this.scroll_to = $el;
		},

		openParents: function(id) {
			var $el = this.getElement(id);

			if (!$el.length) {
				return;
			}

			var parents = [];

			var parent = this.getElementArray($el);
			while (parent) {
				parents[parents.length] = parent;

				parent = this.getParent(parent.el);
			}

			if (!parents.length) {
				return false;
			}

			this.stepThroughParents(parents, null);
		},

		stepThroughParents: function(parents, parent) {
			var self = this;

			if (!parents.length && parent) {
				self.show(parent.id);
				return;
			}

			parent = parents.pop();

			if (parent.el.hasClass('in') || parent.el.parent().hasClass('active')) {
				self.stepThroughParents(parents, parent);
				return;
			}

			switch (parent.type) {
				case 'tab':
					parent.el.one('shown.bs.tab', function() {
						self.stepThroughParents(parents, parent);
					});

					self.show(parent.id);
					break;

				case 'slider':
					if (typeof RegularLabsSliders === 'undefined') {
						self.stepThroughParents(parents, parent);
						break;
					}

					parent.el.one('shown.bs.collapse', function() {
						self.stepThroughParents(parents, parent);
					});

					RegularLabsSliders.show(parent.id);
					break;
			}
		},

		getParent: function($el) {
			if (!$el) {
				return false;
			}

			var $parent = $el.parent().closest('.rl_tabs-pane, .rl_sliders-body');

			if (!$parent.length) {
				return false;
			}

			return this.getElementArray($parent);
		},

		getElementArray: function($el) {
			var id   = $el.attr('data-toggle') ? $el.attr('data-id') : $el.attr('id');
			var type = ($el.hasClass('rl_tabs-pane') || $el.hasClass('rl_tabs-toggle')) ? 'tab' : 'slider'

			return {
				'type': type,
				'id'  : id,
				'el'  : type == 'tab' ? this.getTabElement(id) : this.getSliderElement(id)
			};
		},

		fixEqualHeights: function(parent) {
			var self = this;
			setTimeout((function() {
				self.fixEqualTabHeights(parent);
			}), 250);

			setTimeout((function() {
				self.fixEqualContentHeights(parent);
			}), 500);
		},

		fixEqualTabHeights: function(parent) {
			parent = parent ? 'div.rl_tabs-pane#' + parent.attr('data-id') : 'div.rl_tabs';

			$(parent + ' ul.nav-tabs').each(function() {
				var $lis       = $(this).children();
				var min_height = 9999;
				var max_height = 0;

				// Set heights to auto
				$lis.each(function() {
					$(this).find('a').first().height('auto');
				});

				setTimeout((function() {
					// Get the min and max heights
					$lis.each(function() {
						min_height = Math.min(min_height, $(this).find('a').first().height());
						max_height = Math.max(max_height, $(this).find('a').first().height());
					});

					if (!max_height || min_height == max_height) {
						return;
					}

					// Set all elements in the set to that max height
					$lis.each(function() {
						$(this).find('a').first().height(max_height);
					});
				}), 10);
			});
		},

		initActiveClasses: function() {
			$('li.rl_tabs-tab-sm').removeClass('active');
		},

		updateActiveClassesOnTabLinks: function(active_el) {
			active_el.parent().parent().find('.rl_tabs-toggle').each(function($i, el) {
				$('a.rl_tabs-link[data-id="' + $(el).attr('data-id') + '"]').each(function($i, el) {
					var $link = $(el);

					if ($link.attr('data-toggle') || $link.hasClass('rl_tabs-toggle-sm') || $link.hasClass('rl_sliders-toggle-sm')) {
						return;
					}

					if ($link.attr('data-id') !== active_el.attr('data-id')) {
						$link.removeClass('active');
						return;
					}

					$link.addClass('active');
				});
			});
		},

		initEqualHeights: function() {
			var self = this;

			self.fixEqualHeights();

			$('a.rl_tabs-toggle').on('shown.bs.tab', function(e) {
				self.fixEqualHeights($(this));
			});

			$(window).resize(function() {
				self.fixEqualHeights();
			});
		},

		initHashLinkList: function() {
			var self = this;

			$(
				'a[href^="#"],'
				+ 'a[href^="' + this.current_url + '#"],'
				+ 'a[href^="' + this.current_path + '#"],'
				+ 'a[href^="/' + this.current_path + '#"],'
				+ 'area[href^="#"],'
				+ 'area[href^="' + this.current_url + '#"]'
				+ 'area[href^="' + this.current_path + '#"]'
				+ 'area[href^="/' + this.current_path + '#"]'
			).each(function($i, el) {
				self.initHashLink(el);
			});
		},

		initHashLink: function(el) {
			var self  = this;
			var $link = $(el);

			// link is a tab or slider or list link, so ignore
			if ($link.attr('data-toggle')
				|| $link.hasClass('rl_tabs-toggle')
				|| $link.hasClass('rl_tabs-toggle-sm')
				|| $link.hasClass('rl_sliders-toggle')
				|| $link.hasClass('rl_sliders-link')
			) {
				return;
			}

			var id = $link.attr('href').substr($link.attr('href').indexOf('#') + 1);

			// clean up weird hash values
			id = id.replace(/^\//, '');
			id = id.replace(/^(.*?) .*$/, '$1');

			// No id found
			if (id == '') {
				return;
			}

			var scroll = this.options.linkscroll;

			var is_tab  = true;
			var $anchor = $('a[data-toggle="tab"][data-id="' + id + '"]');

			if (!$anchor.length) {
				$anchor = $('[id="' + id + '"],a[name="' + id + '"]');

				// No accompanying link found
				if (!$anchor.length) {
					return;
				}

				scroll = true;
				is_tab = false;
			}

			$anchor = $anchor.first();

			// Check if anchor has a parent tab
			if (!$anchor.closest('.rl_tabs').length) {
				return;
			}

			// anchor is a tab
			var $tab = $anchor;

			// anchor is not a tab
			if (!$anchor.hasClass('rl_tabs-toggle')) {
				$tab = $anchor.closest('.tab-pane').first();
				$tab = this.getElement($tab.attr('id'));
			}

			var tab_id = $tab.attr('data-id');

			// Check if link is inside the same tab
			if ($link.closest('.rl_tabs').length) {
				if ($link.closest('.tab-pane').first().attr('id') == tab_id) {
					return;
				}
			}

			$link.click(function(e) {
				// Open tab and parents
				e.preventDefault();

				var tab_open  = $tab.parent().hasClass('active');
				var scroll_to = is_tab ? self.getScrollToElement($tab) : $anchor;

				self.showByID(tab_id);

				if (scroll) {
					// Scroll if tab is already open
					if (tab_open || $link.hasClass('rl_tabs-toggle-sm')) {
						$('html,body').animate({scrollTop: scroll_to.offset().top});
						history.replaceState({}, '', self.current_url + '#' + id);
						return;
					}
					// Tab is closed, so scroll when tab is opened
					$tab.one('shown.bs.tab', function(e) {
						$('html,body').animate({scrollTop: scroll_to.offset().top});
						history.replaceState({}, '', self.current_url + '#' + id);
						e.stopPropagation();
					});
				}
				e.stopPropagation();
			});
		},

		initHashHandling: function() {
			if (!window.history.replaceState) {
				return;
			}

			var self = this;

			$('a.rl_tabs-toggle').on('shown.bs.tab', function(e) {
				if ($(this).closest('ul.nav-tabs').hasClass('rl_tabs-slideshow-switch')) {
					return;
				}
				history.replaceState({}, '', self.current_url + '#' + $(this).attr('data-id'));
				e.stopPropagation();
			});
		},

		initClickMode: function() {
			var self = this;

			$('body').on('click.tab.data-api', 'a.rl_tabs-toggle', function(e) {
				var $el = $(this);

				e.preventDefault();

				RegularLabsTabs.show($el.attr('data-id'), $el.hasClass('rl_tabs-doscroll'));

				if (self.timers[$el.closest('ul.nav-tabs').attr('id')]) {
					clearTimeout(self.timers[$el.closest('ul.nav-tabs').attr('id')]);

					if (self.options.stop_slideshow_on_click) {
						self.stop_timers[$el.closest('ul.nav-tabs').attr('id')] = true;
					}
				}

				$el.closest('ul.nav-tabs').removeClass('rl_tabs-slideshow-switch');
				e.stopPropagation();
			});
		},

		initHoverMode: function() {
			var mode = this.options.mode;

			var elements = mode == 'hover'
				? 'a.rl_tabs-toggle'
				: 'li.hover > a.rl_tabs-toggle';

			$(elements).mouseenter(function(e) {
				var $el = $(this);

				if (mode != 'hover' && !$el.parent().hasClass('hover')) {
					return;
				}

				if (mode == 'hover' && $el.parent().hasClass('click')) {
					return;
				}

				e.preventDefault();
				RegularLabsTabs.show($(this).attr('data-id'));
			});
		},

		initCookieHandling: function() {
			var self = this;

			$('a.rl_tabs-toggle').on('show.bs.tab', function(e) {
				var id  = $(this).attr('data-id');
				var $el = self.getElement(id);

				var set = 0;
				$el.closest('ul:not(.dropdown-menu)').each(function($i, el) {
					set = el.id;
				});

				var obj = {};

				var cookies = $.cookie(self.options.cookie_name);
				if (cookies) {
					cookies = cookies.split('___');
					for (var count = 0; count < cookies.length; count++) {
						var keyval = cookies[count].split('=');
						if (keyval.length > 1 && keyval[0] != set) {
							var key = keyval.shift();
							if (key.substr(0, 11) == 'set-rl_tabs') {
								obj[key] = keyval.join('=');
							}
						}
					}
				}
				obj['set-rl_tabs-' + set] = id;

				var arr = [];
				for (var set in obj) {
					if (set && obj[set]) {
						arr[arr.length] = set + '=' + obj[set];
					}
				}

				$.cookie(self.options.cookie_name, arr.join('___'));
			});
		},

		initResponsiveScrolling: function() {
			var self = this;

			$('.nav-tabs-sm a.rl_tabs-link').click(function() {
				var $el = self.getElement($(this).attr('data-id'));
				$('html,body').animate({scrollTop: $el.offset().top});
			});
		},

		initIframeReloading: function() {
			// Mark iframes in active tabs as reloaded
			$('.tab-pane.active iframe').each(function() {
				$(this).attr('reloaded', true);
			});
			// Undo marking of iframes as reloaded in non-active tabs
			$('.tab-pane:not(.active) iframe').each(function() {
				$(this).attr('reloaded', false);
			});

			$('a.rl_tabs-toggle').on('show.bs.tab', function(e) {
				// Re-inintialize Google Maps on tabs show
				if (typeof initialize == 'function') {
					initialize();
				}

				var $el = $('#' + $(this).attr('data-id'));

				$el.find('iframe').each(function() {
					if (!this.src || $(this).attr('reloaded') == 'true') {
						return;
					}

					this.src += '';
					$(this).attr('reloaded', true);
				});
			});

			$(window).resize(function() {
				if (typeof initialize == 'function') {
					initialize();
				}

				$('.tab-pane iframe').each(function() {
					$(this).attr('reloaded', false);
				});

				$('.tab-pane.active iframe').each(function() {
					if (this.src) {
						this.src += '';
						$(this).attr('reloaded', true);
					}
				});
			});
		},

		initSlideshows: function() {
			var self = this;

			$('div.rl_tabs.slideshow ul.nav-tabs').each(function() {
				self.startSlideshow($(this));
			});
		},

		startSlideshow: function($ul) {
			var self = this;

			var timeout = $ul.attr('data-slideshow-timeout');
			timeout     = timeout > 200 ? timeout : this.options.slideshow_timeout;

			$ul.addClass('rl_tabs-slideshow-switch');

			$ul.find('a.rl_tabs-toggle').on('shown.bs.tab', function(e) {
				if (self.stop_timers[$ul.attr('id')] === true) {
					return;
				}

				$ul.addClass('rl_tabs-slideshow-switch');
				self.setNextSlideshow($(this), $ul.attr('id'), timeout);
			});

			$ul.find('.rl_tabs-tab.active a.rl_tabs-toggle').first().each(function() {
				self.setNextSlideshow($(this), $ul.attr('id'), timeout);
			});
		},

		stopSlideshows: function() {
			for (var key in self.timers) {
				this.stopSlideshow(key);
			}
		},

		stopSlideshow: function(id) {
			clearTimeout(this.timers[id]);
		},

		setNextSlideshow: function($el, id, timeout) {
			if (this.stop_timers[id] === true) {
				return;
			}

			var self = this;

			this.stopSlideshow(id);

			self.timers[id] = setTimeout((function() {
				if (!self.openNext($el, true)) {
					self.stopSlideshow(id);
					self.startSlideshow($('#' + id));
				}
			}), timeout);
		},

		openNext: function($el, slideshow) {
			if (!$el || !$el.attr('data-id')) {
				return false;
			}

			var $next = this.getNextTab($el);

			if (!$next || !$next.attr('data-id')) {
				return false;
			}

			if ($el.attr('data-id') == $next.attr('data-id')) {
				return true;
			}

			this.show($next.attr('data-id'), false, false, slideshow);

			return true;
		},

		openPrevious: function($el) {
			if (!$el || !$el.attr('data-id')) {
				return false;
			}

			var $previous = this.getPreviousTab($el);

			if (!$previous || !$previous.attr('data-id')) {
				return false;
			}

			if ($el.attr('data-id') == $previous.attr('data-id')) {
				return true;
			}

			this.show($previous.attr('data-id'));

			return true;
		},

		getNextTab: function($el) {
			if ($el.parent().next().length) {
				return $el.closest('.rl_tabs-tab').next().find('.rl_tabs-toggle').first();
			}

			return $el.closest('ul.nav-tabs').find('.rl_tabs-tab > .rl_tabs-toggle').first();
		},

		getPreviousTab: function($el) {
			if ($el.parent().previous().length) {
				return $el.closest('.rl_tabs-tab').previous().find('.rl_tabs-toggle').first();
			}

			return $el.closest('ul.nav-tabs').find('.rl_tabs-tab > .rl_tabs-toggle').last();
		},

		getUrlVar: function() {
			var search = 'tab';
			var query  = window.location.search.substring(1);

			if (query.indexOf(search + '=') < 0) {
				return '';
			}

			var vars = query.split('&');
			for (var i = 0; i < vars.length; i++) {
				var keyval = vars[i].split('=');

				if (keyval[0] != search) {
					continue;
				}

				return keyval[1];
			}

			return '';
		},

		getOptions: function() {
			if (typeof rl_tabs_options !== 'undefined') {
				return rl_tabs_options;
			}

			if (typeof Joomla === 'undefined' || typeof Joomla.getOptions === 'undefined') {
				console.error('Joomla.getOptions not found!\nThe Joomla core.js file is not being loaded.');
				return false;
			}

			return Joomla.getOptions('rl_tabs');
		}
	};

	$(document).ready(function() {
		var options = RegularLabsTabs.getOptions();

		if (!options) {
			return;
		}

		if (typeof options.init_timeout === 'undefined') {
			return;
		}

		setTimeout(function() {
			RegularLabsTabs.init(options);
		}, options.init_timeout);
	});
})(jQuery);
