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

var ScoopitTopicView = Backbone.View
		.extend({

    events: {
      'click #submit_topic_search': 'onTopicsSearch',
      'change #topic_select_id': 'onChangeTopic'
    },

    initialize: function () {
      this.collection.on('add', this.load, this);
      this.topiclist = '#topic_select_id';
    },

    onTopicsSearch: function onTopicsSearch() {
      var thisView = this,
        list = thisView.$(this.topiclist),
        channelId = thisView.$('#channel_id').val(),
        channelToken = thisView.$('#XTtoken').attr('name'),
        search_topic = thisView.$('#search_topic').val();

      Core.UiHelper.listReset(list);

      this.collection.create(this.collection.model, {
        attrs: {
          channel_id: channelId,
          token: channelToken,
          search: search_topic
        },

        wait: true,
        dataType: 'text',
        error: function (model, fail, xhr) {
          validationHelper.showError(thisView,
            fail.responseText);
        }
      });
    },

    load: function load(message) {
      var thisView = this, topiclist = this.$(this.topiclist), items;

      topiclist.empty();
      if (message.get('status')) {
        items = message.get('topics');
        _.each(items, function (item) {
          var opt = new Option();
          opt.value = item.id;
          opt.text = item.name;
          topiclist.append(opt);
        });
        topiclist.trigger('liszt:updated');
      } else {
        validationHelper.showError(thisView,
          message.get('message'));
      }

    },

    onChangeTopic: function () {
      var topic_id = this.$('#topic_select_id').val();
      this.$('#topic_id').val(topic_id);
    }

		});
