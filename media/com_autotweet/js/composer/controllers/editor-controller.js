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
