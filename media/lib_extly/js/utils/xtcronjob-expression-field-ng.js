/*!
 * @package     Extly.Library
 * @subpackage  lib_extly - Extly Framework
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (C) 2007 - 2017 Extly, CB. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        http://www.extly.com http://support.extly.com
 */

/* globals angular */

'use strict';

angular.module('starter.cronjob-expr-controller', [])
  .controller('CronjobExprController', function ($scope) {

    var local = $scope.cronjobExprCtlr,
      _this = this;

    local.update = function () {
      var mhdmd =
        local.minute_value + " "
        + local.hour_value + " "
        + local.day_value + " "
        + local.month_value + " "
        + local.weekday_value;

      local.unix_mhdmd_value = mhdmd;
    };

    _this.resetControls = function () {
      local.minute_value = "*";
      local.hour_value = "*";
      local.day_value = "*";
      local.month_value = "*";
      local.weekday_value = "*";
    };

    local.reset = function () {
      _this.resetControls();
      local.unix_mhdmd_value = "";
    };

    local.example1 = function () {
      _this.resetControls();

      local.minute_value = "30";
      local.hour_value = "9";

      local.update();
    };

    local.example2 = function () {
      _this.resetControls();

      local.minute_value = "30";
      local.hour_value = "9";
      local.weekday_value = "1";

      local.update();
    };

    local.example3 = function () {
      _this.resetControls();

      local.minute_value = "15";
      local.hour_value = "0,6,12,18";

      local.update();
    };

    local.example4 = function () {
      _this.resetControls();

      local.minute_value = "59";
      local.unix_mhdmd_value = "59 11 * * 1,3,5";
    };

    local.example5 = function () {
      _this.resetControls();

      local.minute_value = "47";
      local.hour_value = "6";
      local.day_value = "8";
      local.month_value = "12";

      local.update();
    };

  });
