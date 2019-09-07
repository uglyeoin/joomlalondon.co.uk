/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* global jQuery, Request, Joomla, alert, Backbone, validationHelper */

"use strict";

var BloggerValidationView = Backbone.View.extend({
  events: {
    'click #bloggervalidationbutton': 'onValidationReq'
  },

  initialize: function () {
    this.collection.on('add', this.loadvalidation, this);
  },

  onValidationReq: function () {
    var view = this,

      channelId = view.$('#channel_id').val().trim(),
      clientSecret = view.$('#client_secret').val().trim(),
      developerKey = view.$('#developer_key').val().trim(),

      token = view.$('#XTtoken').attr('name');

    view.$('#channel_id').val(channelId);
    view.$('#client_secret').val(clientSecret);
    view.$('#developer_key').val(developerKey);

    view.$(".loaderspinner").addClass('loading');

    this.collection.create(this.collection.model, {
      attrs: {
        channel_id: channelId,
        token: token
      },

      wait: true,
      dataType: 'text',
      error: function (model, fail, xhr) {
        view.$(".loaderspinner").removeClass('loading');
        validationHelper.showError(view, fail.responseText);
      }
    });
  },

  loadvalidation: function loadvalidation(resp) {
    var status = resp.get('status'),
      errorMessage = resp.get('message'),
      user = resp.get('user'),
      socialIcon = resp.get('social_icon'),
      socialUrl = resp.get('social_url');

    this.$(".loaderspinner").removeClass('loading');

    if (status) {
      validationHelper.showSuccess(this, user.id, socialIcon, socialUrl);
    } else {
      validationHelper.showError(this, errorMessage);
    }
  }

});
