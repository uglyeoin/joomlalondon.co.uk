/*!
* @package     Extly.Library
* @subpackage  lib_extly - Extly Framework
*
* @author      Extly, CB. <team@extly.com>
* @copyright   Copyright (C) 2007 - 2017 Extly, CB. All rights reserved.
* @license     http://http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
* @link        http://www.extly.com http://support.extly.com
*/

/*global jQuery, Request, Joomla, alert, Backbone, define, angular */

'use strict';

define('extlycore', [], function () {

  var ExtlyModel = Backbone.Model.extend({

    parse: function (resp, options) {
      var body = resp.split(/@EXTLYSTART@|@EXTLYEND@/);
      if (body.length === 3) {
        return JSON.parse(body[1]);
      } else {
        return {
          status: false,
          message: resp
        };
      }
    }

  });

  var UiHelper = {

    listReset: function (theList) {
      var opt;
      opt = new Option();
      opt.value = '0';
      opt.text = '...requesting data...';
      opt.token = '';

      theList.empty();
      theList.append(opt);
      theList.trigger('liszt:updated').trigger('liszt:updated.chosen');
    },

    toggleBtnGroup: function (btn) {
      var parent, active, ref, val, hidden, dataref;

      if (!btn.hasClass('btn')) {
        btn = btn.closest('.btn');
      }

      parent = btn.closest('[data-toggle="buttons-radio"]');
      if (parent) {
        parent.find('.xt-button:not(.active)').removeClass('btn-info');
      }
      active = parent.find('.active');
      active.toggleClass('btn-info');

      dataref = active.attr('data-ref');
      ref = '#' + dataref;
      val = active.attr('data-value');
      hidden = jQuery(ref);
      hidden.val(val);

      if (active.hasClass('onchange-submit')) {
        hidden.get(0).form.submit();
      } else if (window.xtAppDispatcher) {
        window.xtAppDispatcher.trigger('change:' + dataref);
      }
    },

    resetBtnGroup: function (e) {
      var v;

      e = jQuery(e);
      v = e.val();
      e.parent().find('.xt-button').removeClass('active btn-info');
      e.parent().find('.xt-button[data-value="' + v + '"]').addClass('active btn-info');
    }

  };

  var SefHelper = {
    route: function (url) {
      var Itemid = jQuery('#XTItemid').val(),
        lang = jQuery('#XTlang').val();

      if (Itemid) {
        url += '&Itemid=' + Itemid;
      }

      if (lang) {
        url += '&lang=' + lang;
      }

      return url;
    }
  }

  if ((window.Joomla) && (jQuery('.extly .form-validate').length > 0)) {

    Joomla.submitbutton = function (task) {
      var forms = jQuery('.form-validate'), theForm = null;
      if (forms.length === 1) {
        theForm = forms.get(0);
      }
      if ((task === 'cancel')
        || ((!theForm) || (document.formvalidator.isValid(theForm)))) {
        Joomla.submitform(task, jQuery('#adminForm').get(0));
      } else {
        jQuery('#invalid-form').fadeIn();
      }
    };

  }

  var button = jQuery('.xt-button');

  if (button.button) {
    button.button();
  }

  jQuery(document).on('click.button.data-api', '[data-toggle^=button]',
    function (e) {
      var btn = jQuery(e.target);
      e.preventDefault();
      UiHelper.toggleBtnGroup(btn);
    }
  );

  jQuery(document).on('click', 'a.xtd-btn-reset', function (e) {
    var $btn = jQuery(e.target), $td = $btn.closest('td');

    e.preventDefault();
    $td.find('input').val('');
    jQuery('#adminForm').get(0).submit();
		}
  );

  return {
    ExtlyModel: ExtlyModel,
    UiHelper: UiHelper,
    SefHelper: SefHelper,

    // What is the enter key constant?
    ENTER_KEY: 13
  };

});
