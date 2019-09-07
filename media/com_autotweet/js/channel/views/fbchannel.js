/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* global jQuery, Request, Joomla, alert, _, Backbone, validationHelper, appParamsHelper, Core, messagesview */

"use strict";

var FbChannelView = Backbone.View
		.extend({

    events: {
      'change #xtformfbchannel_id': 'onChangeChannel'
    },

    initialize: function () {
      this.attributes.dispatcher.on('fbapp:channelschanged',
        this.onAccessTokenChanged, this);
      this.collection.on('add', this.loadFbChannel, this);

      this.fbchannellist = '#xtformfbchannel_id';
      this.fbChannelSelected = null;

      // this.$('.group-warn').fadeOut();
    },

    onAccessTokenChanged: function () {
      var thisView = this, messagesview = this.attributes.messagesview, params = appParamsHelper
        .get(thisView);

      Core.UiHelper.listReset(thisView.$(this.fbchannellist));

      this.collection.create(this.collection.model, {
        attrs: {
          own_app: params.p_own_app,
          app_id: params.p_app_id,
          secret: params.p_secret,
          access_token: params.p_access_token,
          token: params.p_token,
          channeltype_id: params.p_channelTypeId
        },

        wait: true,
        dataType: 'text',
        error: function (model, fail, xhr) {
          validationHelper.showError(messagesview,
            fail.responseText);
        }
      });
    },

    onChangeChannel: function () {
      var accessToken,
        channelType,
        oselected,
        socialIcon,
        socialUrl,
        xtformshowUrl;

      xtformshowUrl = this.$('#xtformshow_url');
      xtformshowUrl.val('off');
      xtformshowUrl.trigger('liszt:updated');

      this.fbChannelSelected = null;
      accessToken = this.getFbChannelAccessToken();
      channelType = this.getFbChannelType();
      oselected = this.getFbChannelSelected();
      socialIcon = oselected.attr('social_icon');
      socialUrl = oselected.attr('social_url');

      this.$('#fbchannel_access_token').val(accessToken);
      validationHelper.assignSocialUrl(this, 'social_url', socialIcon, socialUrl);

      /*
      if (channelType === 'Group') {
        this.$('.group-warn').fadeIn();
      } else {
        this.$('.group-warn').fadeOut();
      }
      */

      if (channelType === 'User') {
        this.$('.open_graph_features').fadeIn();
      } else {
        this.$('.open_graph_features').fadeOut();
      }

      this.$('.channel-type').val(channelType);
    },

    getFbChannelSelected: function () {
      if (!this.fbChannelSelected) {
        this.fbChannelSelected = this.$(this.fbchannellist + ' option:selected');
      }

      return this.fbChannelSelected;
    },

    getFbChannelAccessToken: function () {
      var oselected = this.getFbChannelSelected(),
        access_token = 'INVALID';
      if (oselected) {
        access_token = oselected.attr('access_token');
      }
      return access_token;
    },

    getFbChannelType: function () {
      var oselected = this.getFbChannelSelected(),
        channelType = 'INVALID';

      if (oselected) {
        channelType = oselected.attr('data_type');
      }

      return channelType;
    },

    getFbChannelId: function () {
      return this.getFbChannelSelected().val();
    },

    loadFbChannel: function (message) {
      var fbchannellist = this.$(this.fbchannellist), channels, socialIcon, first = true;

      fbchannellist.empty();
      this.fbChannelSelected = null;

      if (message.get('status')) {
        channels = message.get('channels');
        socialIcon = message.get('icon');

        _.each(channels, function (channel) {
          var opt = new Option();
          opt.value = channel.id;
          opt.text = channel.type + ': ' + channel.name;

          jQuery(opt)
            .attr('access_token', channel.access_token)
            .attr('data_type', channel.type)
            .attr('social_icon', socialIcon)
            .attr('social_url', channel.url);

          if (first) {
            first = false;
            opt.selected = true;
          }

          fbchannellist.append(opt);
        });

        this.onChangeChannel();

        fbchannellist.trigger('liszt:updated');
      } else {
        validationHelper.showError(this.attributes.messagesview,
          message.get('message'));
      }

    }

		});
