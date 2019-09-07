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

var PushwooshValidationView = Backbone.View.extend({
  events: {
    'click #pushwooshvalidationbutton': 'onValidationReq'
  },

  initialize: function () {
    this.collection.on('add', this.loadvalidation, this);
  },

  onValidationReq: function onValidationReq() {
    "use strict";

    var view = this,
      applicationId = view.$('#application_id').val().trim(),
      accessToken = view.$('#access_token').val().trim(),
      token = view.$('#XTtoken').attr('name');

    view.$('#application_id').val(applicationId);
    view.$('#access_token').val(accessToken);

    view.$(".loaderspinner").addClass('loading');

    this.collection.create(this.collection.model, {
      attrs: {
        application_id: applicationId,
        access_token: accessToken,
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
