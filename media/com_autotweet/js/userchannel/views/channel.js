/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* global angular, _, define, Backbone, UserChannelHelper, define */

'use strict';

var ChannelView = Backbone.View.extend({
		tagName: 'tr',
		template: _.template(channelTemplate),
		events: {
    'click a.publish': 'onPublish',
    'click a.unpublish': 'onUnpublish'
		},
		initialize: function () {
		},
		render: function () {
    var html = this.template(this.model.toJSON());
    this.$el.html(html);
    this.$el.addClass('enabled-channel success');

    return this;
		},

		onPublish: function (e) {
    var view = this;

    this.lastEvent = e;

    // Step 1 - Re-Authorize
    this.attributes.eventsHub.trigger("userChannel:re-authorize", {
      channelId: view.attributes.channelId,
      channelView: this
    });

    e.preventDefault();
    return false;
		},

		onReAuthorized: function () {
    var publishAction = new PublishAction();
    return this.executeAction(this.lastEvent.currentTarget, publishAction);
		},

		onUnpublish: function (e) {
    var unpublishAction = new UnpublishAction();

    e.preventDefault();
    return this.executeAction(e.currentTarget, unpublishAction);
		},

		executeAction: function (target, theAction) {
    var view = this;

    view.attributes.spinner.addClass('loading');
    UserChannelHelper.showError(view, null);

    theAction.fetch({
      data: {
        _token: view.attributes.token,
        channelId: view.attributes.channelId,
        authParams: UserChannelHelper.getAuthParams()
      },

      wait: true,
      dataType: 'text',
      success: function (message, resp, options) {
        view.attributes.spinner.removeClass('loading');

        if (message.get('status')) {
          view.model = message;
          view.render();
        } else {
          UserChannelHelper.showError(view, message);
        }
      },
      error: function (model, fail, xhr) {
        view.attributes.spinner.removeClass('loading');
        UserChannelHelper.showError(view, fail.responseText);
      }
    });

    return false;
		}
});

// 'click a.authorize-enabled' : 'onAuthorize',

/*
onAuthorize : function(e) {
	var authorizeAction = new AuthorizeAction();

	e.preventDefault();
	return this.executeAction(e.currentTarget, authorizeAction);
},
*/
