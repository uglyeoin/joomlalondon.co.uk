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

var EnabledView = Backbone.View.extend({

  initialize: function () {
    this.attributes.eventsHub.on('userChannel:authorized',
      this.addAuthChannel, this);
    this.$enabledList = this.$('#enabledList');
    this.collection.on('add', this.addOne, this);

    this.token = this.$('#XTtoken').attr('name');
    this.spinner = this.$(".loaderspinner");

    this.initializeSubviews();
  },

  addAuthChannel: function () {
    var view = this;

    view.spinner.addClass('loading');
    UserChannelHelper.showError(view, null);

    this.collection.create(this.collection.model, {
      attrs: {
        authParams: UserChannelHelper.getAuthParams(),
        token: view.token
      },

      wait: true,
      dataType: 'text',
      success: function (model, resp, options) {
        view.spinner.removeClass('loading');

        if (!model.get('status')) {
          UserChannelHelper.showError(view, model || 'Unknown error (EnabledView)');
        }
      },
      error: function (model, fail, xhr) {
        view.spinner.removeClass('loading');
        UserChannelHelper.showError(view, fail.responseText);
      }
    });

    return false;
  },
  addOne: function (channel) {
    var channelView;

    if (!channel.get('status')) {
      return false;
    }

    channelView = new ChannelView({
      model: channel,

      // Options
      attributes: {
        channelId: channel.get('id'),
        token: this.token,
        spinner: this.spinner,
        eventsHub: this.attributes.eventsHub
      }
    });

    this.$enabledList.removeClass('hide');
    this.$enabledList.append(channelView.render().el);
  },
  initializeSubviews: function (channel) {
    var view = this,
      channels = view.$('tr.enabled-channel'),
      channelId;

    _.each(channels, function (channel) {
      channelId = view.$(channel).find('.channel_id').val();

      new ChannelView({
        el: channel,
        model: (new Core.ExtlyModel()),

        // Options
        attributes: {
          channelId: channelId,
          token: view.token,
          spinner: view.spinner,
          eventsHub: view.attributes.eventsHub
        }
      });
    });
  }
});

