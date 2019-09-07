/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* global angular */

'use strict';

angular.module('starter.directives', [])
  .directive('LoadingContainer', function () {

    return {
      restrict: 'A',
      scope: false,
      link: function (scope, element, attrs) {
        var loadingLayer = angular
          .element('<span class="loaderspinner72">loading...</span>');
        element.append(loadingLayer);
        element.addClass('loading-container');
        scope.$watch(attrs.loadingContainer, function (value) {
          loadingLayer.toggleClass('ng-hide', !value);
        });
      }
    };
  });

/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* globals jQuery, angular, Joomla, _ */

'use strict';

angular.module('starter.jquery-extras', [])
  .run(function () {
    angular.element(document).ready(function () {

      var form = document.getElementById('adminForm'),
        $form = jQuery(form);

      // Social Attributes Tabs and fields group - Action on click
      $form.find('.post-attrs-group a').click(function (e) {
        var btn = jQuery(e.target), v;

        if (btn.hasClass('xticon')) {
          btn = btn.parent('a');
        }

        v = btn.attr('data-value');

        $form.find('.xt-subform').hide();
        $form.find('.xt-subform-' + v).show();
      });

      // Hide Social Attributes Tabs
      $form.find('.xt-subform').css('display', 'none');
    });
  });
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

angular.module('starter.requests-service', ['extlycore', 'ngResource'])
  .factory('Requests', ['SefHelper', '$resource',
    function (SefHelper, $resource) {

      var url = SefHelper.route('index.php?option=com_autotweet&view=requests&format=json');
      var xTtoken = jQuery('#XTtoken').attr('name');

      var jsonParse = function (data) {
        var body = data.split(/@EXTLYSTART@|@EXTLYEND@/);

        if (body.length === 3) {
          return JSON.parse(body[1]);
        } else {
          return {
            status: false,
            message: data
          };
        }
      };

      return $resource(url, {}, {
        query: {
          method: 'POST',
          params: {
            task: '@taskCommand',
            _token: xTtoken
          },
          isArray: true,
          transformResponse: function (data, headersGetter) {
            return jsonParse(data);
          }
        },
        save: {
          method: 'POST',
          params: {
            task: '@taskCommand',
            _token: xTtoken,
            ref_id: '@ref_id'
          },
          transformResponse: function (data, headersGetter) {
            return jsonParse(data);
          }
        },
        get: {
          method: 'POST',
          params: {
            task: '@taskCommand',
            _token: xTtoken,
            id: '@request_id'
          },
          transformResponse: function (data, headersGetter) {
            return jsonParse(data);
          }
        }
      });
    }]);
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

/**
 * Services that persists and retrieves Agendas from localStorage
 */
angular.module('starter.agenda-service', [])
  .factory('Agenda', function () {

    var STORAGE_ID = 'agendas-autotweet',
      _this = this;

    _this.get = function () {
      return JSON.parse(localStorage.getItem(STORAGE_ID) || '[]');
    };

    _this.put = function (todos) {
      localStorage.setItem(STORAGE_ID, JSON.stringify(todos));
    };

    _this.clear = function () {
      this.put([]);
    };

    return {
      get: _this.get,
      put: _this.put,
      clear: _this.clear
    };

  });
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* globals angular, _, CryptoJS */

'use strict';

angular
  .module('starter.request-helper', [])
  .factory(
  'RequestHelper',
  [function () {
    var _this = this;

    _this.formatDatesTimes = function (agendas) {
      var dates = [];

      _.each(agendas, function (agenda) {
        dates.push(agenda.agendaDate + ' ' + agenda.agendaTime);
      });

      return dates;
    };

    _this.hasPastDates = function (agendas) {
      var today = new Date(), isPastDate = false;

      if ((!agendas) || (agendas.length === 0)) { return false; }

      try {
        _
          .each(
          agendas,
          function (dayTime) {
            var d, dateString = dayTime;

            if (dayTime
              .match(/^\d\d\d\d-\d\d-\d\d \d?\d:\d\d$/)) {
              dateString = dayTime
                .replace(' ', 'T')
                + ':00';
            }

            if (dayTime
              .match(/^\d\d\d\d-\d\d-\d\d \d?\d:\d\d:\d\d$/)) {
              dateString = dayTime
                .replace(' ', 'T');
            }

            d = new Date(dateString);

            if (d.getTime() < today.getTime()) {
              isPastDate = true;
            }
          });
      } catch (e) {
        isPastDate = true;
      }

      return isPastDate;
    };

    _this.getChannels = function (channels) {
      if ((_.isNumber(channels)) && (channels > 0)) {
        return [channels];
      }

      if ((_.isString(channels)) && (parseInt(channels) > 0)) {
        return [channels];
      }

      if (_.isArray(channels)) {
        return channels;
      }

      return [];
    };

    _this.getChannelsText = function (channels) {
      var options, channelsText;

      if (!window.channelchooser) { return ''; }

      options = angular.element(window.channelchooser).find(
        'option');

      options = _.filter(options, function (option) {
        return (option.selected) && (parseInt(option.value) > 0);
      });

      channelsText = _.reduce(options, function (memo, option) {
        var e = angular.element(option), txt = e.text();

        if (_.isEmpty(memo)) {
          return txt;
        } else {
          return memo + ', ' + txt;
        }
      }, '');

      return channelsText;
    };

    _this.isValidAgendaRepeat = function (agendas, unixMhdmd) {
      var invalid = ((unixMhdmd) && (unixMhdmd.length > 0) && (agendas.length > 1));

      return !invalid;
    };

    _this.generateRequestHash = function () {
      var now = new Date(), hash;

      hash = CryptoJS.MD5('' + now.getTime()
        + _.random(0, 9007199254740992));
      hash = CryptoJS.MD5(hash + _.random(0, 9007199254740992));
      hash = CryptoJS.MD5(hash + _.random(0, 9007199254740992));

      return hash.toString(CryptoJS.enc.Hex);
    };

    _this.isValidCronjobExpr = function (expr) {
      var testExpr = /^(((([0-9]+)(\,[0-9]+)*)|\*) ){4}((([0-9]+)(\,[0-9]+)*)|\*)$/;

      return ((_.isEmpty(expr)) || (testExpr.test(expr)));
    };

    return {
      formatDatesTimes: _this.formatDatesTimes,
      hasPastDates: _this.hasPastDates,
      getChannelsText: _this.getChannelsText,
      isValidAgendaRepeat: _this.isValidAgendaRepeat,
      generateRequestHash: _this.generateRequestHash,
      getChannels: _this.getChannels,
      isValidCronjobExpr: _this.isValidCronjobExpr
    };
  }]);
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

angular
  .module(
  'starter.editor-controller',
  ['starter.requests-service', 'starter.agenda-service',
    'starter.request-helper',

    'ui.bootstrap.buttons', 'localytics.directives'])
  .controller(
  'EditorController',
  function ($scope, $rootScope, $sce, Requests, Agenda,
    RequestHelper) {

    var _this = this;
    var local = $scope.editorCtrl;

    local.waiting = false;
    local.showDialog = false;

    _this.saveAndLoad = false;

    local.addRequest = function (e) {
      var descr, request, params = {}, attrs = {}, agendas = [], unixMhdmdValue;

      e.preventDefault();
      local.showDialog = false;

      // Operation in progress, go home
      if (local.waiting) {
        _this.error({
          messageType: 'error',
          message: 'Please, try to save it again.'
        });

        return;
      }

      // Read it from Message Controller field - description_value
      descr = angular.element(window.description).val();

      // IE 11 workaround - head description tag preference ...
      if ((!window.description) && (_.isUndefined(descr))) {
        descr = angular.element(document.getElementById('description')).val();
      }

      descr = descr.trim();

      // No message
      if (descr.length === 0) {
        _this
          .error({
            messageType: 'error',
            message: 'Empty message. Please, fill the main text field.'
          });

        return;
      }

      agendas = Agenda.get();
      agendas = RequestHelper.formatDatesTimes(agendas);

      // Check Agenda
      if (RequestHelper.hasPastDates(agendas)) {
        _this
          .error({
            messageType: 'error',
            message: 'Agenda has dates in the past. Please, remove them.'
          });

        return false;
      }

      // From CronjobExprController field - unixMhdmdValue
      unixMhdmdValue = null;

      if (window.unix_mhdmd) {
        unixMhdmdValue = angular.element(window.unix_mhdmd)
          .val();

        // Valid Expression
        if (!RequestHelper.isValidCronjobExpr(unixMhdmdValue)) {
          _this
            .error({
              messageType: 'error',
              message: 'Repeat Expression is invalid.'
            });

          return false;
        }

        // Check Agenda
        if (!RequestHelper.isValidAgendaRepeat(agendas,
          unixMhdmdValue)) {
          _this
            .error({
              messageType: 'error',
              message: 'Repeat Expression not allowed for more than one Agenda date.'
            });

          return false;
        }
      }

      params.plugin = local.plugin;
      params.description = descr;
      params.url = local.url_value;

      // Just in case jQuery Selection
      local.image_url_value = angular.element(window.image_url)
        .val();
      params.image_url = local.image_url_value;

      // Read it from Message Controller field - hashtags_value
      if (window.hashtags) {
        params.hashtags = angular.element(window.hashtags)
          .val();
      } else {
        params.hashtags = '';
      }

      // autotweet_advanced_attrs
      attrs.ref_id = local.ref_id;
      attrs.fulltext = local.fulltext_value;

      if (window.itemeditor_postthis) {
        attrs.postthis = angular.element(
          window.itemeditor_postthis).val();
      } else {
        attrs.postthis = null;
      }

      if (window.itemeditor_evergreen) {
        attrs.evergreen = angular.element(
          window.itemeditor_evergreen).val();
      } else {
        attrs.evergreen = null;
      }

      attrs.unix_mhdmd = unixMhdmdValue;
      attrs.repeat_until = local.repeat_until_value;
      attrs.channels = local.channelchooser_value;
      attrs.channels_text = RequestHelper
        .getChannelsText(local.channelchooser_value);
      attrs.agenda = agendas;
      attrs.image = '';

      if (local.option_filter) {
        attrs.option_filter = local.option_filter;
      }

      // Control parameters
      if (local.plugin == 'autotweetpost') {
        params.taskCommand = 'applyAjaxOwnAction';
      } else {
        params.taskCommand = 'applyAjaxPluginAction';
      }

      params.option = local.option;
      params.view = local.view;
      params.task = local.task;
      params.returnurl = local.returnurl;
      params[angular.element(window.XTtoken).attr('name')] = 1;
      params.lang = angular.element(window.XTlang).val();
      params.published = local.published;
      params.ajax = 1;

      // Advanced Attrs Reference
      params.ref_id = local.ref_id;

      // FoF Model
      params.id = local.request_id;

      params.autotweet_advanced_attrs = JSON.stringify(attrs);

      local.waiting = true;
      request = new Requests(params);
      request.$save(null, _this.success, _this.error);
    };

    _this.success = function (response) {
      local.showDialog = true;
      local.messageResult = response.status;
      local.messageText = $sce.trustAsHtml(response.message);

      local.request_id = 0;
      local.ref_id = response.hash;
      local.waiting = false;

      if ((response.status) && (_this.redirectOnSuccess)) {
        Joomla.submitbutton('cancel');
      }

      _this.redirectOnSuccess = false;

      if (_this.saveAndLoad) {
        $rootScope.$emit('editRequest', response.request_id);
        _this.saveAndLoad = false;
      } else {
        _this.reset();
      }

      $rootScope.$emit('newRequest');
    };

    _this.error = function (response) {
      local.showDialog = true;
      local.messageResult = false;
      local.messageText = $sce.trustAsHtml('Error: '
        + response.message);

      _this.redirectOnSuccess = false;
      local.waiting = false;
      $scope.$digest();
    };

    _this.callbackAddRequest = function (event) {
      _this.saveAndLoad = false;
      _this.addRequest(event);
    };

    _this.callbackAddRequestLoad = function (event) {
      _this.saveAndLoad = true;
      _this.addRequest(event);
    };

    _this.callbackAddRequestAndRedirect = function (event) {
      _this.redirectOnSuccess = true;
      _this.callbackAddRequestLoad(event);
    };

    local.menuitemlistHide = function () {
      var el = document.getElementById('menulist_group'), menuitemlist = angular
        .element(el);

      if (menuitemlist.hasClass('hide')) {
        menuitemlist.removeClass('hide');
      } else {
        menuitemlist.addClass('hide');
      }
    };

    _this.load = function (request) {
      _this.reset();
      local.waiting = false;

      local.messageResult = 'success';
      local.messageText = $sce.trustAsHtml('-Loaded-');

      // Read-only fields (Ng-value)
      local.request_id = request.id;
      local.ref_id = request.ref_id;
      local.plugin = request.plugin;

      // Shared with MessageController
      $rootScope.$emit('updateMessageView', request.description,
        request.xtform.hashtags);

      local.url_value = request.url;

      // Integrated with Joomla Media Manager
      local.image_url_value = request.image_url;

      // Delay to refresh
      setTimeout(function () {
        window.xtRefreshPreview('', 'image_url');
      }, 1);

      if (request.autotweet_advanced_attrs) {
        local.fulltext_value = request.autotweet_advanced_attrs.fulltext;

        // Attrs Option filter
        local.option_filter = request.autotweet_advanced_attrs.option;

        // Shared with AgendaController
        $rootScope.$emit('loadAgenda',
          request.autotweet_advanced_attrs.agenda);

        // Generate click to set values - delay to avoid inprog
        // error
        setTimeout(
          function () {
            angular
              .element(
              document
                .getElementById('ctrl_itemeditor_postthis_'
                + request.autotweet_advanced_attrs.postthis))
              .click();
            angular
              .element(
              document
                .getElementById('ctrl_itemeditor_evergreen_'
                + request.autotweet_advanced_attrs.evergreen))
              .click();
          }, 1);

        local.channelchooser_value = request.autotweet_advanced_attrs.channels;

        // Shared with CronjobExprController field
        angular.element(window.unix_mhdmd).val(
          request.autotweet_advanced_attrs.unix_mhdmd);

        local.repeat_until_value = request.autotweet_advanced_attrs.repeat_until;
      }
    };

    _this.getHash = function () {
      return RequestHelper.generateRequestHash();
    };

    _this.reset = function () {
      local.showDialog = false;
      local.request_id = 0;
      local.ref_id = _this.getHash();

      local.plugin = 'autotweetpost';

      // Shared with MessageController
      $rootScope.$emit('updateMessageView', '', '');

      local.url_value = '';
      local.menuitem_value = '';
      local.fulltext_value = '';

      local.image_url_value = '';
      window.xtRefreshPreview('', 'image_url');
      angular.element(document.getElementById('image_url-image'))
        .html('');

      // Generate click to set values - delay to avoid inprog
      // error
      setTimeout(
        function () {
          // 1 - Default
          angular
            .element(
            document
              .getElementById('ctrl_itemeditor_postthis_1'))
            .click();

          // 2 - No
          angular
            .element(
            document
              .getElementById('ctrl_itemeditor_evergreen_2'))
            .click();
        }, 1);

      local.channelchooser_value = [];

      // From CronjobExprController field - unixMhdmdValue
      if (window.unix_mhdmd) {
        angular.element(window.unix_mhdmd).val('');
      }

      local.repeat_until_value = '';
      $rootScope.$emit('loadAgenda', []);

      if (window.unix_mhdmd_minute) {
        angular.element(window.unix_mhdmd_minute).val('*');
        angular.element(window.unix_mhdmd_hour).val('*');
        angular.element(window.unix_mhdmd_day).val('*');
        angular.element(window.unix_mhdmd_month).val('*');
        angular.element(window.unix_mhdmd_weekday).val('*');
      }
    };

    _this.editRequest = function (e, requestId) {
      var request, params = {};

      e.preventDefault();
      local.showDialog = false;

      // Operation in progress, go home
      if (local.waiting) {
        _this.error({
          messageType: 'error',
          message: 'Please, try to save it again.'
        });

        return;
      }

      if (!requestId) { return; }

      params.id = requestId;
      params.request_id = requestId;
      params.taskCommand = 'readAjaxAction';
      params.ajax = 1;

      local.waiting = true;
      request = new Requests(params);
      request.$get(null, _this.load, _this.error);
    };

    _this.publishRequest = function (e, requestId) {
      var request, params = {};

      e.preventDefault();
      local.showDialog = false;

      // Operation in progress, go home
      if (local.waiting) {
        _this.error({
          messageType: 'error',
          message: 'Please, try to publish it again.'
        });

        return;
      }

      if (!requestId) { return; }

      params.id = requestId;
      params.request_id = requestId;
      params.taskCommand = 'publishAjaxAction';
      params.ajax = 1;

      local.waiting = true;
      request = new Requests(params);
      request.$save(null, _this.success, _this.error);
    };

    _this.cancelRequest = function (e, requestId) {
      var request, params = {};

      e.preventDefault();
      local.showDialog = false;

      // Operation in progress, go home
      if (local.waiting) {
        _this.error({
          messageType: 'error',
          message: 'Please, try to cancel it again.'
        });

        return;
      }

      if (!requestId) { return; }

      params.id = requestId;
      params.request_id = requestId;
      params.taskCommand = 'cancelAjaxAction';
      params.ajax = 1;

      local.waiting = true;
      request = new Requests(params);
      request.$save(null, _this.success, _this.error);
    };

    _this.loadUrl = function (itemId) {
      var request, params = {};

      local.showDialog = false;

      // Operation in progress, go home
      if (local.waiting) {
        _this.error({
          messageType: 'error',
          message: 'Unable to load Urls, Please, try it again.'
        });

        return;
      }

      params.itemId = itemId;
      params.taskCommand = 'routeAjaxItemId';
      params.ajax = 1;

      local.waiting = true;
      request = new Requests(params);
      request.$save(null, _this.successRoute, _this.error);
    };

    _this.successRoute = function (response) {
      local.url_value = response.url;
      local.waiting = false;
    };

    _this.reset();

    $rootScope.$on('editRequest', _this.editRequest);
    $rootScope.$on('publishRequest', _this.publishRequest);
    $rootScope.$on('cancelRequest', _this.cancelRequest);

    // Joomla 3 - Back-end Site
    var holder = document.getElementById('toolbar-apply'), buttons;

    // Apply
    if (holder) {
      angular.element(holder).find('button')
        .attr('onclick', null).click(
        _this.callbackAddRequestLoad);
    }

    holder = document.getElementById('toolbar-save');

    // Save
    if (holder) {
      angular.element(holder).find('button')
        .attr('onclick', null).click(
        _this.callbackAddRequestAndRedirect);
    }

    holder = document.getElementById('toolbar-save-new');

    // Savenew
    if (holder) {
      angular.element(holder).find('button')
        .attr('onclick', null).click(
        _this.callbackAddRequest);
    }

    // Joomla 3 - Front-end Site
    holder = document.getElementById('F0FHeaderHolder');
    buttons = angular.element(holder).find('button');

    _.each(buttons, function (button, i) {
      // Apply
      if (i === 0) {
        angular.element(button).attr('onclick', null).click(
          _this.callbackAddRequestLoad);
      }

      // Save
      if (i === 1) {
        angular.element(button).attr('onclick', null).click(
          _this.callbackAddRequestAndRedirect);
      }

      // Savenew
      if (i === 2) {
        angular.element(button).attr('onclick', null).click(
          _this.callbackAddRequest);
      }
    });
  });
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

angular
  .module('starter.agenda-controller', ['starter.agenda-service'])
  .controller(
  'AgendaController',
  function ($scope, $rootScope, Agenda) {

    var agendaCtrl = $scope.agendaCtrl, agendas = agendaCtrl.agendas = Agenda
      .get();

    Agenda.clear();

    agendaCtrl.add = function () {
      var schedulingDate = agendaCtrl.scheduling_date_value;
      var schedulingTime = agendaCtrl.scheduling_time_value;

      if ((!schedulingDate) || (!schedulingTime)) {
        return;
      }

      schedulingDate = schedulingDate.trim();
      if (!schedulingDate.length) {
        return;
      }

      agendas.push({
        agendaDate: schedulingDate,
        agendaTime: schedulingTime
      });

      Agenda.put(agendas);

      agendaCtrl.scheduling_date_value = '';
    };

    agendaCtrl.remove = function (agenda) {
      agendas.splice(agendas.indexOf(agenda), 1);
      Agenda.put(agendas);
    };

    $rootScope.$on('newRequest', function () {
      Agenda.clear();
      agendas = agendaCtrl.agendas = Agenda.get();
    });

    $rootScope.$on('loadDate', function (event, schedulingDate) {
      agendaCtrl.scheduling_date_value = schedulingDate;
    });

    $rootScope.$on('loadTime', function (event, schedulingTime) {
      agendaCtrl.scheduling_time_value = schedulingTime;
    });

    $rootScope.$on('loadAgenda', function (event, param) {
      var output = [];

      _.each(param, function (item) {
        var schedulingDate, schedulingTime, parts = item
          .split(' ');

        schedulingDate = parts[0];
        schedulingTime = parts[1];

        // 14:45:00
        parts = schedulingTime.split(':');

        if (parts.length == 3) {
          parts.splice(2, 1);
          schedulingTime = parts.join(':');
        }

        output.push({
          agendaDate: schedulingDate,
          agendaTime: schedulingTime
        });
      });

      Agenda.put(output);
      agendas = agendaCtrl.agendas = Agenda.get();
      agendaCtrl.scheduling_date_value = '';
    });
  });
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
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* global angular */

"use strict";

(function () {
  var deps = ['starter.message-controller',
    'starter.editor-controller',
    'starter.requests-controller',
    'starter.agenda-controller',
    'starter.jquery-extras'];

  // Joocial
  try {
    angular.module('starter.cronjob-expr-controller');
    deps.push('starter.cronjob-expr-controller');
  } catch (e) {
  }

  angular.module('starter', deps)
    .config(function ($logProvider, $compileProvider) {

      // Debug Application
      $logProvider.debugEnabled(false);
      $compileProvider.debugInfoEnabled(false);

    });
})();
