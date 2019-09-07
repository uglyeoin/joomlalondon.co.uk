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

var LiOAuth2CompanyView = Backbone.View
		.extend({

    events: {
      'click #lioauth2companyloadbutton': 'onChangeChannel',
      'change #xtformcompany_id': 'onChangeCompany'
    },

    initialize: function () {
      this.collection.on('add', this.loadLiCompany, this);
      this.lioauth2companylist = '#xtformcompany_id';
      this.$('.group-warn').fadeOut();
    },

    onChangeChannel: function () {
      var thisView = this,
        channel_id = thisView.$('#channel_id').val().trim(),
        token = thisView.$('#XTtoken').attr('name');

      Core.UiHelper.listReset(thisView.$(this.lioauth2companylist));

      this.collection.create(this.collection.model, {
        attrs: {
          channel_id: channel_id,
          token: token
        },

        wait: true,
        dataType: 'text',
        error: function (model, fail, xhr) {
          validationHelper.showError(this, fail.responseText);
        }
      });
    },

    loadLiCompany: function (message) {
      var lioauth2companylist = this.$(this.lioauth2companylist),
        channels,
        socialIcon,
        first = true;

      lioauth2companylist.empty();

      if (message.get('status')) {
        channels = message.get('channels');
        socialIcon = message.get('icon');

        _.each(channels, function (channel) {
          var opt = new Option();
          opt.value = channel.id;
          opt.text = channel.name;

          jQuery(opt)
            .attr('social_icon', socialIcon)
            .attr('social_url', channel.url);

          if (first) {
            first = false;
            opt.selected = true;
          }

          lioauth2companylist.append(opt);
        });

        this.onChangeCompany();
        validationHelper.showSuccess(this, '');

        lioauth2companylist.trigger('liszt:updated');
      } else {
        validationHelper.showError(this, message.get('message'));
      }

    },

    onChangeCompany: function () {
      var oselected = this.$('#xtformcompany_id option:selected'),
        socialIcon = oselected.attr('social_icon'),
        socialUrl = oselected.attr('social_url');

      validationHelper.assignSocialUrl(this, 'social_url_lioauth2company', socialIcon, socialUrl);
    }

		});
