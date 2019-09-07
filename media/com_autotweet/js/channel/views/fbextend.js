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

var FbExtendView = Backbone.View.extend({
  events: {
    'click #fbextendbutton': 'onExtendReq'
  },

  initialize: function () {
    var view = this;

    this.collection.on('add', this.loadExtend, this);

    this.$el.ajaxStart(function () {
      view.$(".loaderspinner72").addClass('loading72');
    }).ajaxStop(function () {
      view.$(".loaderspinner72").removeClass('loading72');
    });
  },

  onExtendReq: function () {
    var view = this, params = appParamsHelper.get(view);

    this.collection.create(this.collection.model, {
      attrs: {
        own_app: params.p_own_app,
        app_id: params.p_app_id,
        secret: params.p_secret,
        access_token: params.p_access_token,
        token: params.p_token
      },

      wait: true,
      dataType: 'text',
      error: function (model, fail, xhr) {
        validationHelper.showError(view, fail.responseText);
      }
    });
  },

  loadExtend: function (resp) {
    var status = resp.get('status'),
      error_message = resp.get('message'),
      user,
      extended_token,
      tokenInfo,
      issued_at,
      expires_at;

    if (status) {
      user = resp.get('user');
      extended_token = resp.get('extended_token');
      tokenInfo = resp.get('tokenInfo');
      issued_at = tokenInfo.issued_at;
      expires_at = tokenInfo.expires_at;

      if (user) {
        validationHelper.showSuccess(this, user.id);
      } else {
        validationHelper.showSuccess(this, tokenInfo.data.user_id);
      }

      this.$('#access_token').val(extended_token);
      this.$('#issued_at').val(issued_at);
      this.$('#expires_at').val(expires_at);

      this.attributes.dispatcher.trigger("fbapp:channelschanged");
    } else {
      validationHelper.showError(this, error_message);
    }
  }

});
