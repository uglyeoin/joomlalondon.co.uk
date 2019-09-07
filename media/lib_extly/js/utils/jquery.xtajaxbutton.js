/*!
 * jquery.xtajaxbutton.js 1.0.0 - https://github.com/anibalsanchez/jquery.saveform.js
 * Saves automatically all entered form fields, to restore them in the next visit.
 *
 * Copyright (c) 2013 Anibal Sanchez (http://www.extly.com) Licensed under the MIT
 * license (http://www.opensource.org/licenses/mit-license.php). 2013/05/04
 *
 * Based on the original work of Yannick Albert (http://yckart.com)
 * jquery.saveform.js 0.0.1 - https://github.com/yckart/jquery.saveform.js
 */

/* globals jQuery */

'use strict';

; (function ($, window) {

  $.fn.xtajaxbutton = function (callBack) {
    var $this = this;
    var theCallBack = callBack;

    $this.click(function (e) {
      e.preventDefault();
    });

    function clickAndCall(e) {
      var urlToCall = e.currentTarget.get('href');

      $(".extly .loaderspinner").addClass('loading');
      $.ajax({
        url: urlToCall,
        complete: function () {
          $(".extly .loaderspinner").removeClass('loading');
        },
        success: function (data, textStatus, jqXHR) {
          var body = jqXHR.responseText.split(/@EXTLYSTART@|@EXTLYEND@/), status;
          //input = $this.next('.xt-ajax-message');
          if (body.length === 3) {
            status = !theCallBack || theCallBack(body[1], e.currentTarget);
          }
          else {
            status = !theCallBack || theCallBack(jqXHR.responseText, e.currentTarget);
          }
        },
        error: function (jqXHR, textStatus, errorThrown) {
          ((!theCallBack) || theCallBack(textStatus));
        }
      });
    }

    $this.on({
      click: clickAndCall
    });


  };
} (jQuery, window));