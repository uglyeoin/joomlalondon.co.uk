/*!
 * @package     Extly.Library
 * @subpackage  lib_extly - Extly Framework
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (C) 2007 - 2017 Extly, CB. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        http://www.extly.com http://support.extly.com
 */

/*global define, angular */

'use strict';

(
  function (factory) {

    if (typeof define === 'function' && define.amd) {
      define('xtcronjob-expression-field', [], function () {
        return factory();
      });
    } else {
      if (window.angular) {
        angular.element(document).ready(factory);
        return true;
      } else {
        return factory();
      }
    }
		} (function () {

    var _this = this || {};

    var theForm = jQuery('.cronjob-expression-form');
    var shortcutsArea = jQuery('.shortcuts');

    var minuteCtrl = theForm.find('.minute-part');
    var hourCtrl = theForm.find('.hour-part');
    var dayCtrl = theForm.find('.day-part');
    var monthCtrl = theForm.find('.month-part');
    var weekdayCtrl = theForm.find('.weekday-part');

    var allCtrls = minuteCtrl.add(hourCtrl).add(dayCtrl).add(monthCtrl).add(weekdayCtrl);

    var unixMhdmd = theForm.find('.unix_mhdmd-part');

    _this.onReset = function () {
      allCtrls.val('*').trigger('liszt:updated').trigger('liszt:updated.chosen');
      unixMhdmd.val('');
    };

    _this.onChangeMhdmd = function () {
      var minute2 = minuteCtrl.val();
      var hour2 = hourCtrl.val();
      var day2 = dayCtrl.val();
      var month2 = monthCtrl.val();
      var weekday2 = weekdayCtrl.val();

      var mhdmd = minute2 + " " + hour2 + " " + day2 + " " + month2 + " " + weekday2;
      unixMhdmd.val(mhdmd);
    };

    allCtrls.change(_this.onChangeMhdmd);

    shortcutsArea.find('.reset').click(_this.onReset);

    shortcutsArea.find('.example1').click(function () {
      _this.onReset();
      minuteCtrl.val('30').trigger('liszt:updated').trigger('liszt:updated.chosen');
      hourCtrl.val('9').trigger('liszt:updated').trigger('liszt:updated.chosen');
      _this.onChangeMhdmd();
    });

    shortcutsArea.find('.example2').click(function () {
      _this.onReset();
      minuteCtrl.val('30').trigger('liszt:updated').trigger('liszt:updated.chosen');
      hourCtrl.val('9').trigger('liszt:updated').trigger('liszt:updated.chosen');
      weekdayCtrl.val('1').trigger('liszt:updated').trigger('liszt:updated.chosen');
      _this.onChangeMhdmd();
    });

    shortcutsArea.find('.example3').click(function () {
      _this.onReset();
      minuteCtrl.val('15').trigger('liszt:updated').trigger('liszt:updated.chosen');
      hourCtrl.val('0,6,12,18').trigger('liszt:updated').trigger('liszt:updated.chosen');
      _this.onChangeMhdmd();
    });

    shortcutsArea.find('.example4').click(function () {
      _this.onReset();
      minuteCtrl.val('59').trigger('liszt:updated').trigger('liszt:updated.chosen');
      hourCtrl.val('11').trigger('liszt:updated').trigger('liszt:updated.chosen');
      unixMhdmd.val('59 11 * * 1,3,5');
    });

    shortcutsArea.find('.example5').click(function () {
      _this.onReset();
      minuteCtrl.val('47').trigger('liszt:updated').trigger('liszt:updated.chosen');
      hourCtrl.val('6').trigger('liszt:updated').trigger('liszt:updated.chosen');
      dayCtrl.val('8').trigger('liszt:updated').trigger('liszt:updated.chosen');
      monthCtrl.val('12').trigger('liszt:updated').trigger('liszt:updated.chosen');
      _this.onChangeMhdmd();
    });
  })
);
