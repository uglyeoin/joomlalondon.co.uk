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

var PushAlertValidationView = Backbone.View.extend({
  events: {
    'click #pushalertvalidationbutton': 'onValidationReq'
  },

  initialize: function () {
    this.collection.on('add', this.loadvalidation, this);
  },

  onValidationReq: function onValidationReq() {
    "use strict";

    var view = this,
      restApiKey = view.$('#rest_api_key').val().trim(),
      token = view.$('#XTtoken').attr('name');

    view.$('#rest_api_key').val(restApiKey);

    view.$(".loaderspinner").addClass('loading');

    this.collection.create(this.collection.model, {
      attrs: {
        rest_api_key: restApiKey,
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
