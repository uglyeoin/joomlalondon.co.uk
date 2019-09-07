/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* globals angular,_ */

'use strict';

angular.module('starter.message-controller', []).controller(
  'MessageController',
  function ($scope, $rootScope) {

    var _this = this || {};
    var local = $scope.messageCtrl;

    local.remainingCount = 0;
    local.remainingCountClass = '';

    local.countRemaining = function () {
      var style, c, h;

      c = (local.description_value ? local.description_value.length : 0);
      h = (_.isEmpty(_this.hashtags_value) ? 0 : _this.hashtags_value.length + 1);

      c = c + h;
      style = 'label';

      if ((c > 0) && (c <= 60)) {
        style = 'label label-success';
      } else if ((c > 60) && (c <= 100)) {
        style = 'label label-warning';
      } else if (c > 100) {
        style = 'label label-important';
      }

      local.remainingCount = c;
      local.remainingCountClass = style;
    };

    _this.updateMessageView = function (e, desc, hash) {
      local.description_value = desc;

      if (window.hashtags) {
        local.hashtags_value = hash;
      }

      local.countRemaining();
    };

    $rootScope.$on('updateMessageView', _this.updateMessageView);

  });
