/*!
* @package     Extly.Library
* @subpackage  lib_extly - Extly Framework
*
* @author      Extly, CB. <team@extly.com>
* @copyright   Copyright (C) 2007 - 2017 Extly, CB. All rights reserved.
* @license     http://http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
* @link        http://www.extly.com http://support.extly.com
*/

/*global jQuery, Request, Joomla, alert, Backbone, define, angular, UiHelper */

'use strict';

angular.module('extlycore', [])
  .factory('SefHelper', [function () {
    var route = function (url) {
      var Itemid = jQuery('#XTItemid').val(),
        lang = jQuery('#XTlang').val();

      if (Itemid) {
        url += '&Itemid=' + Itemid;
      }

      if (lang) {
        url += '&lang=' + lang;
      }

      return url;
    };

    return {
      route: route
    };
  }]);

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
