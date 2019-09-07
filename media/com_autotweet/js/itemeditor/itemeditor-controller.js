/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* global angular,_,unix_mhdmd,description,hashtags,itemeditor_postthis,itemeditor_evergreen,image_url */

'use strict';

angular.module('starter.itemeditor-controller', [
  'starter.helper',
  'starter.agenda-service',
  'starter.request-helper',
  'ui.bootstrap.buttons',
  'localytics.directives'])
  .controller('ItemEditorController', function ($scope, $rootScope, ItemEditorHelper, RequestHelper, Agenda) {

    var local = $scope.itemEditorCtrl,
      _this = this;

    local.showDialog = false;
    local.messageText = '';

    _this.assignImage = function (e, imageUrl) {
      if (!_.isEmpty(imageUrl)) {
        local.image_url_value = imageUrl;
      }
    };

    _this.itemeditorInit = function () {
      var attrs;

      attrs = ItemEditorHelper.getHtmlAdvancedAttrs();
      if (attrs) {
        $rootScope.$emit('loadMessage', attrs);
      }
    };

    _this.loadMessage = function (e, message) {
      if (_.isEmpty(message.description)) {
        message.description = ItemEditorHelper.retrieveTitle();
      }

      if (_.isEmpty(message.fulltext)) {
        message.fulltext = ItemEditorHelper.retrieveFulltext();
      }

      // Shared with MessageController
      $rootScope.$emit('updateMessageView', message.description, message.hashtags);

      local.fulltext_value = message.fulltext;

      $rootScope.$emit('loadImages');
      local.image_url_value = message.image_url;

      // Shared with AgendaController
      $rootScope.$emit('loadAgenda', message.agenda);

      // Radio Buttons - Generate click to set values - delay to avoid inprog error
      setTimeout(function () {
        angular.element(document.getElementById('ctrl_itemeditor_postthis_'
          + message.postthis)).click();
        angular.element(document.getElementById('ctrl_itemeditor_evergreen_'
          + message.evergreen)).click();
      }, 1);

      local.channelchooser_value = message.channels;

      // Shared with CronjobExprController field
      angular.element(unix_mhdmd).val(message.unix_mhdmd);

      local.repeat_until_value = message.repeat_until;
    };

    local.onSubmit = function () {
      var descr, message, agendas, unixMhdmdValue;

      // Read it from Message Controller field - description_value
      descr = angular.element(description).val();
      descr = descr.trim();

      message = parent.autotweetAdvancedAttrs || {};
      message.description = descr;

      // Read it from Message Controller field - hashtags_value
      message.hashtags = angular.element(hashtags).val();

      message.fulltext = local.fulltext_value;

      // Radio Buttons
      message.postthis = angular.element(itemeditor_postthis).val();
      message.evergreen = angular.element(itemeditor_evergreen).val();

      agendas = Agenda.get();
      agendas = RequestHelper.formatDatesTimes(agendas);

      // Check Agenda
      if (RequestHelper.hasPastDates(agendas)) {
        _this.error({
          statusText: 'Agenda has dates in the past. Please, remove them.'
        });

        return false;
      }

      // From CronjobExprController field - unixMhdmdValue
      unixMhdmdValue = angular.element(unix_mhdmd).val();

      // Valid Expression
      if (!RequestHelper.isValidCronjobExpr(unixMhdmdValue)) {
        _this.error({
          statusText: 'Repeat Expression is invalid.'
        });

        return false;
      }

      // Check Agenda
      if (!RequestHelper.isValidAgendaRepeat(agendas, unixMhdmdValue)) {
        _this.error({
          statusText: 'Repeat Expression not allowed for more than one Agenda date.'
        });

        return false;
      }

      message.agenda = agendas;
      message.unix_mhdmd = unixMhdmdValue;
      message.repeat_until = local.repeat_until_value;

      // Just in jQuery Selection
      if (_.isEmpty(local.image_url_value)) {
        local.image_url_value = angular.element(image_url).val();
      }

      message.image_url = local.image_url_value;
      message.channels = local.channelchooser_value;
      message.channels_text = RequestHelper.getChannelsText(local.channelchooser_value);

      ItemEditorHelper.loadPanel(message);
      ItemEditorHelper.setHtmlAdvancedAttrs(message);

      // window.parent.xtModalClose();
      var btn = window.parent.jQuery('.mce-close');

      if (!btn.length) {
        btn = window.parent.jQuery('#joocial_menu_modal .modal-header button');
      }

      btn.click();

      if (window.parent.jModalClose) {
        window.parent.jModalClose();
      }
    };

    _this.error = function (msg) {
      local.showDialog = true;
      local.messageText = msg.statusText;
    };

    $rootScope.$on('loadMessage', _this.loadMessage);
    $rootScope.$on('selectedImage', _this.assignImage);
    $rootScope.$on('itemeditorInit', _this.itemeditorInit);
  })
  .run(function ($rootScope) {
    angular.element(document).ready(function () {
      $rootScope.$emit('itemeditorInit');
    });
  });
