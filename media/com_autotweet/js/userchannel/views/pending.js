/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* global angular, _, define, Backbone, UserChannelHelper, define */

'use strict';

var PendingView = Backbone.View.extend({

  events: {
    'click a.authorize-pending': 'onAuthorize'
  },
  initialize: function () {
    jQuery('#F0FHeaderHolder').hide();

    this.attributes.eventsHub.on('userChannel:re-authorize',
      this.onReAuthorize, this);
    this.token = this.$('#XTtoken').attr('name');
    this.spinner = this.$(".loaderspinner");
    this.$pendingChannel = null;
  },
  onAuthorize: function (e) {
    var view = this,
      $targetChannelType,
      channelTypeId;

    e.preventDefault();

    $targetChannelType = view.$(e.currentTarget);
    this.$pendingChannel = $targetChannelType.parents('.pending-channel');
    channelTypeId = this.$pendingChannel.find('.channeltype_id').val();

    this.spinner.addClass('loading');
    UserChannelHelper.showError(view, null);

    this.collection.create(this.collection.model, this.getAuthParamsCallback(view, {
      attrs: {
        channelTypeId: channelTypeId,
        token: view.token
      }
    }));

    return false;
  },
  getAuthParamsCallback: function (view, attrs) {
    var options = {
      wait: true,
      dataType: 'text',
      success: function (model, resp, options) {
        var status, params, callback;

        view.spinner.removeClass('loading');

        if (model.get('status')) {
          callback = model.get('callback');
          params = model.get('params');

          status = UserChannelHelper[callback](params, view);

          if (status === true) {
            view.authorizedChannel();
          } else if (status === false) {
            UserChannelHelper.showError(view, UserChannelHelper.getStatusMessage() || 'Unknown error (PendingView 2)');
          }
          // status = null => Authorizing
        } else {
          UserChannelHelper.showError(view, model || 'Unknown error (PendingView)');
        }
      },
      error: function (model, fail, xhr) {
        UserChannelHelper.showError(view, fail.responseText);
      }
    };

    return _.extend(options, attrs);
  },
  authorizedChannel: function () {

    // A new channel
    if (this.$pendingChannel) {
      this.$pendingChannel.remove();
      this.$pendingChannel = null;

      this.$('#no-auth-channels-msg').remove();
      this.attributes.eventsHub.trigger("userChannel:authorized");
    } else {
      // Re-authorizing an existing channel
      this.channelView.onReAuthorized();
      this.channelView = null;
    }
  },
  onReAuthorize: function (params) {
    var pending = new this.collection.model(),
      view = this;

    this.channelView = params.channelView;

    pending.save(null, this.getAuthParamsCallback(view, {
      attrs: {
        channelId: params.channelId,
        token: view.token
      }
    }));
  },
  showError: function (view, responseText) {
    if (responseText) {
      view.$('#alert-msg').removeClass('hide');
      view.$('#error-msg').html(responseText);
    } else {
      view.$('#alert-msg').addClass('hide');
    }
  }

});
