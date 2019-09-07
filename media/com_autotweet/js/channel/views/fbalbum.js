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

var FbAlbumView = Backbone.View
		.extend({

    events: {
      'click #fbalbumloadbutton': 'onAlbumsReq'
    },

    initialize: function () {
      this.collection.on('add', this.loadFbAlbum, this);
      this.fbalbumlist = '#xtformfbalbum_id';
    },

    onAlbumsReq: function onAlbumsReq() {
      var thisView = this,
        params = appParamsHelper.get(thisView),
        list = thisView.$(this.fbalbumlist),
        fbChannelView = this.attributes.fbChannelView,
        channelId = fbChannelView.getFbChannelId(),
        channelToken = fbChannelView.getFbChannelAccessToken();

      Core.UiHelper.listReset(list);

      this.collection.create(this.collection.model, {
        attrs: {
          own_app: params.p_own_app,
          app_id: params.p_app_id,
          secret: params.p_secret,
          access_token: params.p_access_token,
          channel_id: channelId,
          channel_access_token: channelToken,
          token: params.p_token
        },

        wait: true,
        dataType: 'text',
        error: function (model, fail, xhr) {
          validationHelper.showError(messagesview,
            fail.responseText);
        }
      });
    },

    loadFbAlbum: function loadFbAlbum(message) {
      var fbalbumlist = this.$(this.fbalbumlist), albums;

      fbalbumlist.empty();
      if (message.get('status')) {
        albums = message.get('albums');
        _.each(albums, function (album) {
          var opt = new Option();
          opt.value = album.id;
          opt.text = album.name;
          fbalbumlist.append(opt);
        });
        fbalbumlist.trigger('liszt:updated');
      } else {
        validationHelper.showError(this.attributes.messagesview,
          message.get('message'));
      }

    }

		});
