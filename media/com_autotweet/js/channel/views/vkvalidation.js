/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* global jQuery, Request, Joomla, alert, _, Backbone, validationHelper, appParamsHelper, Core, messagesview, FB */

"use strict";

var VkValidationView = Backbone.View.extend({
  events: {
    'click #authorizeButton': 'onAuthorization',
    'click #vkvalidationbutton': 'onValidationReq'
  },

  initialize: function () {
    this.collection.on('add', this.loadvalidation, this);
  },

  onAuthorization: function onAuthorization() {
    this.$('#authorizeGroup').addClass('hide');
    this.$('#validationGroup').removeClass('hide');
  },

  processTokenUrl: function processTokenUrl(view) {
    var hash, params, access_token = {};

    // Access token is coming

    hash = view.$('#token_url').val().trim();
    params = hash.split('#');

    if (_.size(params) == 2) {
      hash = params[1];
    } else {
      return false;
    }

    if (!_.isEmpty(hash)) {
      params = hash.split('&');
      _.each(params, function (param) {
        var kv = param.split('='), k, v;

        if (_.size(kv) == 2) {
          k = kv[0];
          v = kv[1];

          jQuery('#raw_' + k).val(v);

          access_token[k] = v;
        }
      }
      );

      jQuery('#access_token').val(JSON.stringify(access_token));

      return true;
    }

    return false;
  },

  onValidationReq: function onValidationReq() {
    var view = this,
      channel_id = view.$('#channel_id').val(),
      access_token,
      token = view.$('#XTtoken').attr('name');

    if (!this.processTokenUrl(view)) {
      validationHelper.showError(view, 'Invalid Token Url');
    }

    access_token = view.$('#access_token').val();

    view.$(".loaderspinner").addClass('loading');

    this.collection.create(this.collection.model, {
      attrs: {
        channel_id: channel_id,
        access_token: access_token,
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
      error_message = resp.get('message'),
      socialIcon = resp.get('social_icon'),
      socialUrl = resp.get('social_url');

    this.$(".loaderspinner").removeClass('loading');

    if (status) {
      validationHelper.showSuccess(this, error_message, socialIcon, socialUrl);
    } else {
      validationHelper.showError(this, error_message);
    }
  }

});
