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

var OneSignalValidationView = Backbone.View.extend({
  events: {
    'click #oneSignalvalidationbutton': 'onValidationReq'
  },

  initialize: function () {
    this.collection.on('add', this.loadvalidation, this);
  },

  onValidationReq: function onValidationReq() {
    "use strict";

    var view = this,
      appId = view.$('#app_id').val().trim(),
      restApiKey = view.$('#rest_api_key').val().trim(),
      userAuthKey = view.$('#user_auth_key').val().trim(),
      token = view.$('#XTtoken').attr('name');

    view.$('#app_id').val(appId);
    view.$('#rest_api_key').val(restApiKey);
    view.$('#user_auth_key').val(userAuthKey);

    view.$(".loaderspinner").addClass('loading');

    this.collection.create(this.collection.model, {
      attrs: {
        app_id: appId,
        rest_api_key: restApiKey,
        user_auth_key: userAuthKey,
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
      errorMessage = resp.get('message');

    this.$(".loaderspinner").removeClass('loading');

    if (status) {
      validationHelper.showSuccess(this);
    } else {
      validationHelper.showError(this, errorMessage);
    }
  }

});
