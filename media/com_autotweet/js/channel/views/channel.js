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

var ChannelView = Backbone.View.extend({
  events: {
    'change #channeltype_id': 'onChangeChannelType'
  },

  initialize: function () {
    var view = this;
    var selectedScope = view.$('#selectedScope').val();

    this.collection.on('add', this.loadchannel, this);

    // User Channels cannot be saved here
    if (selectedScope == 'U') {
      jQuery("#toolbar-save,#toolbar-apply,#toolbar-save-new")
        .addClass('disabled')
        .attr('onclick', 'return false;');
    }
  },

  onChangeChannelType: function onChangeChannelType(e) {
    var view = this,
      channelTypeId = this.$('#channeltype_id').val();

    view.$(".loaderspinner").addClass('loading');

    this.collection.create(this.collection.model, {
      attrs: {
        channelId: this.$('#channel_id').val(),
        channelTypeId: channelTypeId,
        token: this.$('#XTtoken').attr('name')
      },

      wait: true,
      dataType: 'text',

      success: function (model, resp, options) {
        view.$('#channel_data').html(model.get('message'));
        view.refresh();
      },

      error: function (model, fail, xhr) {
        view.$('#channel_data').html(fail.responseText);
      }
    });
  },

  loadchannel: function loadchannel(paramsform) {
    var msg = paramsform.get('message');
    this.$('#channel_data').html(msg);
    this.refresh();
  },

  refresh: function refresh() {
    // Enable Chosen in selects
    this.$('#channel_data select').chosen({
      disable_search_threshold: 10,
      allow_single_deselect: true
    });

    // Activate Tabs
    this.$('#channel_data .nav-tabs a').tab();
    this.$('#channel_data .nav-tabs a').click(function (e) {
      e.preventDefault();
    });

    this.$('#channel_data .nav-tabs a:first').tab('show');

    this.$('#channelTabs a').tab();

    this.$('#channelTabs a').click(function (e) {
      e.preventDefault();
    });

    this.$('#channelTabs a:first').tab('show');
  },

  submitbutton: function submitbutton(task) {
    var isValid, domform = this.el;

    if (task === 'channel.cancel') {
      Joomla.submitform(task, domform);
    }

    isValid = document.formvalidator.isValid(domform);
    if (isValid) {
      Joomla.submitform(task, domform);
    }
  }

});
