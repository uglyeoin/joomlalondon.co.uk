/**
 * @package         Tooltips
 * @version         7.4.1PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2019 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

var RegularLabsTooltipsPopup = null;

(function($) {
	"use strict";

	RegularLabsTooltipsPopup = {
		params   : {
			mode     : ['hover', 'sticky', 'click'],
			position : ['top', 'bottom', 'left', 'right'],
			tip_type : ['text', 'image'],
			link_type: ['text', 'image']
		},
		internals: ['tip_type', 'link_type'],

		init: function() {
			$('.reglab-overlay').css('cursor', '').fadeOut();
		},

		setDefault: function(el) {
			$('.' + $(el).parent().attr('id') + '_icon').show();
			this.closeOtherDefaults(el);
		},

		closeOtherDefaults: function(el) {
			var self = this;
			var $el  = $(el);

			$('input[name$="[default]"][value="1"]').each(function($i, input) {
				if ($(input).attr('name') == $el.attr('name')) {
					return;
				}

				$('.' + $(input).parent().attr('id') + '_icon').hide();
				self.setRadioOption($(input).attr('name'), 0);
			});
		},

		insertText: function() {
			var self = this;

			var tip = this.getTip();

			if (tip == '') {
				alert(window['tooltips_error_empty_' + this.getValue('tip_type')]);
				return false;
			}

			var html = tooltips_tag_characters[0] + tooltips_tag + ' ' + tip + tooltips_tag_characters[1]
				+ this.getLink()
				+ tooltips_tag_characters[0] + '/' + tooltips_tag + tooltips_tag_characters[1];

			window.parent.jInsertEditorText(html, tooltips_editorname);

			return true;
		},

		getTypeValue: function(group, escape) {
			var type = this.getValue(group + '_type');

			if (type == 'image') {
				return this.getValue(group + '_image');
			}

			return this.getValue(group + '_text', escape);
		},

		getTip: function() {
			var self = this;
			var tip  = this.getTypeValue('tip', true);

			if (tip == '') {
				return '';
			}

			var tip_type = this.getValue('tip_type');

			if (tip_type == 'image') {
				var tip_image_attribs = this.getValue('tip_image_attribs');

				tip = 'image="' + tip + '"';
				if (tip_image_attribs) {
					tip += ' image_' + tip_image_attribs.replace(/" ([a-z])/g, '" image_$1');
				}
			} else if (this.getValue('tip_title')) {
				tip = 'title="' + this.getValue('tip_title', true) + '" content="' + tip + '"';
			} else {
				tip = 'content="' + tip + '"';
			}

			var tip_class = this.getValue('class');

			if (tip_class) {
				tip += ' class="' + tip_class + '"';
			}

			$.each(this.params, function(param_type, param_set) {
				if (self.internals.indexOf(param_type) > -1) {
					return;
				}

				var val = self.getValue(param_type);

				if (!val) {
					return;
				}

				tip += ' ' + param_type + '="' + val + '"';
			});

			return tip;
		},

		getLink: function() {
			var link = this.getTypeValue('link');

			if (link == '') {
				return tooltips_text_placeholder;
			}

			if (this.getValue('link_type') != 'image') {
				return link;
			}

			return link = '<img src="' + link + '" ' + this.getValue('link_image_attribs') + ' />';
		},

		getValue: function(name, escape) {
			var field = $('[name="tooltip\\[' + name + '\\]"]');

			if (field.attr('type') == 'radio') {
				field = $('[name="tooltip\\[' + name + '\\]"]:checked');
			}

			var value = field.val().trim();

			if (escape) {
				value = value.replace(/"/g, '\\"');
			}

			return value;
		},

		setRadioOption: function(name, value) {
			var name   = name.replace('[', '\\[').replace(']', '\\]');
			var inputs = $('input[name="' + name + '"]');
			var input  = $('input[name="' + name + '"][value="' + value + '"]');

			$('label[for="' + input.attr('id') + '"]').click();
			inputs.attr('checked', false);
			input.attr('checked', true).click();
		},

		setSelectOption: function(name, value) {
			var name   = name.replace('[', '\\[').replace(']', '\\]');
			var select = $('select[name="' + name + '"]');
			var option = $('select[name="' + name + '"] option[value="' + value + '"]');

			select.attr('value', value).click();
			select.attr('selected', true).click();
		}
	};

	$(document).ready(function() {
		setTimeout(function() {
			RegularLabsTooltipsPopup.init();
		}, 1000);
	});
})(jQuery);
