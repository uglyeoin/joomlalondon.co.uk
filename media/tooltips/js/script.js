/**
 * @package         Tooltips
 * @version         7.4.1PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2019 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

var RegularLabsTooltips = null;

(function($) {
	"use strict";

	RegularLabsTooltips = {
		options   : {},
		timeout   : null,
		timeoutOff: false,

		init: function(options) {
			var self = this;

			options      = options ? options : this.getOptions();
			this.options = options;

			// hover mode
			$('.rl_tooltips-link.hover').popover({
				trigger  : 'hover',
				container: 'body',
				delay    : {show: 5, hide: options.delay_hide}
			});

			// click mode
			$('.rl_tooltips-link.click').popover({trigger: 'manual', container: 'body'})
				.click(function(evt) {
					self.show($(this), evt, 'click');
				})
				.mouseout(function(evt) {
					self.setTimer($(this), options.timeout);
				});

			// sticky mode
			$('.rl_tooltips-link.sticky').popover({trigger: 'manual', container: 'body'})
				.mouseover(function(evt) {
					self.show($(this), evt, 'sticky');
				})
				.mouseout(function(evt) {
					self.setTimer($(this), options.timeout);
				});

			// close all popovers on click outside
			$('html').click(function() {
				$('.rl_tooltips-link').popover('hide');
			});

			// do stuff differently for touchscreens
			$('html').one('touchstart', function() {
				// add click mode for hover mode
				$('.rl_tooltips-link.hover').popover({
					trigger  : 'manual',
					container: 'body'
				}).click(function(evt) {
					self.show($(this), evt, 'click');
					self.setTimer($(this), options.delay_hide_touchscreen);
				});
				// add click mode for sticky mode
				$('.rl_tooltips-link.sticky').popover({
					trigger  : 'manual',
					container: 'body'
				}).click(function(evt) {
					self.show($(this), evt, 'click');
					self.setTimer($(this), options.timeout);
				});
			});

			// close all popovers on click outside
			$('html').on('touchstart', function(e) {
				if ($(e.target).closest('.rl_tooltips').length) {
					return;
				}

				$('.rl_tooltips-link').popover('hide');
			});

			$('.rl_tooltips-link').on('touchstart', function(evt) {
				// prevent click close event
				evt.stopPropagation();
			});

			// Adds delay hide functionality to hover popup
			var parentHide = $.fn.popover.Constructor.prototype.hide;

			$.fn.popover.Constructor.prototype.hide = function(callback) {
				if (options.trigger === "hover" && this.tip().hasClass('hover')) {
					var self = this;
					// try again after what would have been the delay
					setTimeout(function() {
						return self.hide.call(self, callback);
					}, options.delay.hide);
					return;
				}

				parentHide.call(this, callback);
			};

			// Improved placement of tooltip if there is no space for it in area
			if (options.use_auto_positioning) {
				$.fn.popover.Constructor.prototype.show = function() {
					var el = this;
					self.fixPlacement(el);
				}
			}
		},

		show: function(el, event, classname) {
			var self = this;

			// prevent other click events
			event.stopPropagation();

			clearTimeout(this.timeout);

			var popover = typeof el.data('popover') !== 'undefined' ? el.data('popover') : el.data('bs.popover');

			// close all other popovers
			$('.rl_tooltips-link.' + classname).each(function() {
				var popover2 = typeof $(this).data('popover') !== 'undefined' ? $(this).data('popover') : $(this).data('bs.popover');

				if (popover2 != popover) {
					$(this).popover('hide');
				}
			});

			// open current
			if (!popover.tip().hasClass('in')) {
				el.popover('show');
			}

			$('.rl_tooltips')
				.click(function(evt) {
					// prevent click close event on popover
					evt.stopPropagation();

					// switch timeout off for this tooltip
					self.timeoutOff = true;
					clearTimeout(self.timeout);
				})
				.mouseover(function(evt) {
					clearTimeout(self.timeout);
				})
				.mouseout(function(evt) {
					self.setTimer(el, self.options.timeout);
				})
			;
		},

		setTimer: function(el, timeout) {
			// check if timeout should be set
			if (!this.timeoutOff && timeout) {
				// set the timeout
				this.timeout = setTimeout(function(el) {
					el.popover('hide');
				}, timeout, el);
			}
		},

		fixPlacement: function(el) {
			if (!el.hasContent() || !el.enabled) {
				return;
			}

			var event = $.Event('show.bs.' + el.type);

			if (event.isDefaultPrevented()) {
				return;
			}

			var self = this;
			var $tip = el.tip();

			$tip
				.mouseover(function(evt) {
					$tip.addClass('hover');
				})
				.mouseout(function(evt) {
					$tip.removeClass('hover');
				});

			el.setContent();

			if (el.options.animation) {
				$tip.addClass('fade');
			}

			setTimeout(function() {
				self.setPlacement(el);
			}, 10);

			el.$element.trigger('shown.bs.' + el.type);
		},

		setPlacement: function(el) {
			var $tip = el.tip();

			var placement = (typeof el.options.placement == 'function') ?
				el.options.placement.call(el, $tip[0], el.$element[0]) :
				el.options.placement;

			$tip.detach().css({top: 0, left: 0, display: 'block'});

			el.options.container ? $tip.appendTo(el.options.container) : $tip.insertAfter(el.$element);

			var newPosition;
			var position     = el.getPosition();
			var actualWidth  = $tip[0].offsetWidth;
			var actualHeight = $tip[0].offsetHeight;

			// Get positions
			var positionTop    = {top: position.top - actualHeight, left: position.left + position.width / 2 - actualWidth / 2};
			var positionBottom = {top: position.top + position.height, left: position.left + position.width / 2 - actualWidth / 2};
			var positionLeft   = {top: position.top + position.height / 2 - actualHeight / 2, left: position.left - actualWidth};
			var positionRight  = {top: position.top + position.height / 2 - actualHeight / 2, left: position.left + position.width};

			// Get position room
			var hasRoomTop    = (positionTop.top > $(window).scrollTop());
			var hasRoomBottom = ((positionBottom.top + actualHeight) < ($(window).scrollTop() + $(window).height()));
			var hasRoomLeft   = (positionLeft.left > $(window).scrollLeft());
			var hasRoomRight  = ((positionRight.left + actualWidth) < ($(window).scrollLeft() + $(window).width()));

			switch (placement) {
				case 'top':
					if (!hasRoomTop) {
						placement = hasRoomBottom ? 'bottom' : (hasRoomRight ? 'right' : (hasRoomLeft ? 'left' : this.options.fallback_position));
					}
					break;
				case 'bottom':
					if (!hasRoomBottom) {
						placement = hasRoomTop ? 'top' : (hasRoomRight ? 'right' : (hasRoomLeft ? 'left' : this.options.fallback_position));
					}
					break;
				case 'left':
					if (!hasRoomLeft) {
						placement = hasRoomRight ? 'right' : (hasRoomTop ? 'top' : (hasRoomBottom ? 'bottom' : this.options.fallback_position));
					}
					break;
				case 'right':
					if (!hasRoomRight) {
						placement = hasRoomLeft ? 'left' : (hasRoomTop ? 'top' : (hasRoomBottom ? 'bottom' : this.options.fallback_position));
					}
					break;
			}

			switch (placement) {
				case 'top':
					newPosition = positionTop;
					break;
				case 'bottom':
					newPosition = positionBottom;
					break;
				case 'left':
					newPosition = positionLeft;
					break;
				case 'right':
					newPosition = positionRight;
					break;
			}

			el.applyPlacement(newPosition, placement);
			$tip.removeClass('top bottom left right').addClass(placement);

		},

		getOptions: function() {
			if (typeof rl_tooltips_options !== 'undefined') {
				return rl_tooltips_options;
			}

			if (typeof Joomla === 'undefined' || typeof Joomla.getOptions === 'undefined') {
				console.error('Joomla.getOptions not found!\nThe Joomla core.js file is not being loaded.');
				return false;
			}

			return Joomla.getOptions('rl_tooltips');
		}
	};

	$(document).ready(function() {
		RegularLabsTooltips.init();
	});
})(jQuery);
