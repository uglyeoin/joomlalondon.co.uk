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

var PageSpeedValidationView = Backbone.View.extend({
  events: {
    'click #pagespeedvalidationbutton': 'onValidationReq'
  },

  initialize: function () {
    this.collection.on('add', this.loadvalidation, this);
  },

  onValidationReq: function () {
    var view = this,

      channelId = view.$('#channel_id').val().trim(),
      apiKey = view.$('#api_key').val().trim(),

      token = view.$('#XTtoken').attr('name');

    view.$('#channel_id').val(channelId);
    view.$('#api_key').val(apiKey);

    view.$(".loaderspinner").addClass('loading');

    this.collection.create(this.collection.model, {
      attrs: {
        channel_id: channelId,
        api_key: apiKey,
        token: token,
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
      errorMessage = resp.get('message');

    this.$(".loaderspinner").removeClass('loading');

    if (status) {
      validationHelper.showSuccess(this, null, null, null);
    } else {
      validationHelper.showError(this, errorMessage);
    }
  }

});
