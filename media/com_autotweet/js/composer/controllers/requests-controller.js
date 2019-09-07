/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* globals angular, Joomla, _ */

'use strict';

angular.module('starter.requests-controller', ['starter.requests-service', 'ngTable'])
  .controller('RequestsController', function ($scope, $timeout, $rootScope, Requests, ngTableParams) {

    var _this = this,
      local = $scope.requestsCtrl,
      element = document.getElementById('list_limit'),
      listLimit = angular.element(element).val();

    local.firstTime = true;

    local.requestsTable = new ngTableParams({
      count: listLimit
    }, {
        total: 0,

        // Hides page sizes
        counts: [],
        getData: function ($defer, params) {
          local.waiting = true;

          Requests.query({

            // Controller
            taskCommand: 'browse',
            ajax: 1,

            // Pagination
            boxchecked: 0,
            hidemainmenu: 0,
            filter_order: 'publish_up',
            filter_order_Dir: 'ASC',
            limitstart: 0,
            limit: listLimit,

            // Filters
            publish_up: 0,
            search: '',
            plugin: 0,
            published: 0

          }, function (data) {
            $timeout(function () {
              local.waiting = false;
              $defer.resolve(data);

              if (local.firstTime) {
                local.firstTime = false;
                _this.loadReqParam(data);
              };
            }, 500);
          });
        }
      });

    _this.loadReqParam = function (data) {
      var reqId = _this.getQueryParams('req-id');

      // There is an Url request to select a req-id
      if (!reqId) {
        return false;
      }

      // Wait until data is loaded
      data.$promise.then(function (data) {
        var i = 0;

        // Load the request in the editor
        $rootScope.$emit('editRequest', reqId);

        // Select it in the table
        _.each(data, function (item) {
          if (item.id == reqId) {
            data[i].$selected = true;
          }

          i++;
        });
      });
    };

    _this.getQueryParams = function (queryString) {
      var query = (window.location.search).substring(1);

      var obj = _.chain(query.split('&')).map(function (params) {
        var p = params.split('=');
        return [p[0], decodeURIComponent(p[1])];
      }).object().value();

      return obj[queryString];
    };

    local.requestsTable.doRefresh = function () {
      local.requestsTable.reload();
    };

    local.requestsTable.selectRow = function (row) {
      for (var i = 0; i < local.requestsTable.data.length; i++) {
        local.requestsTable.data[i].$selected = false;
      }
      row.$selected = true;
    };

    local.requestsTable.editRow = function (row) {
      $rootScope.$emit('editRequest', row.id);
    };

    local.requestsTable.publishRow = function (row) {
      $rootScope.$emit('publishRequest', row.id);
    };

    local.requestsTable.cancelRow = function (row) {
      $rootScope.$emit('cancelRequest', row.id);
    };

    $rootScope.$on('newRequest', local.requestsTable.doRefresh);
  });
