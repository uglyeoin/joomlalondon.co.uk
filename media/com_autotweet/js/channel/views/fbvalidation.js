/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* global jQuery, Request, alert, Backbone, validationHelper, appParamsHelper, FB */

'use strict';

var FbValidationView = Backbone.View.extend({
  events: {
    'click #fbAsyncInitButton': 'onFbAsyncInit',
    'click #fbvalidationbutton': 'onValidationReq',
    'click #fbChannelTab': 'onCheckFbApp',
    'input #app_id': 'onCheckFbApp',
    'input #secret': 'onCheckFbApp',
  },

  initialize: function () {
    var view = this;

    this.attributes.dispatcher.on('change:use_own_api',
      this.onChangeOwnApi, this);
    this.attributes.dispatcher.on('change:og_features',
      this.onChangeOgFeatures, this);

    this.collection.on('add', this.loadvalidation, this);

    this.$el.ajaxStart(function () {
      view.$('.loaderspinner72').addClass('loading72');
    }).ajaxStop(function () {
      view.$('.loaderspinner72').removeClass('loading72');
      view.onCheckFbApp();
    });

    this.onChangeOwnApi();
  },

  onChangeOwnApi: function (e) {
    var ownApp = this.$('#use_own_api').val();

    // No or Yes, with Canvas Page
    var authorizeCanvas = (ownApp != '2');

    if (authorizeCanvas) {
      this.$('#canvas_page').addClass('required').addClass('validate-facebookapp');

      this.$('#authextendbutton').fadeOut(0);
      this.$('#authbutton').fadeIn(0);

      this.$('#fbextendbutton').fadeOut(0);
      this.$('#fbvalidationbutton').fadeIn(0);
    }
    else {
      this.$('#canvas_page').removeClass('required').removeClass('validate-facebookapp');

      this.$('#authextendbutton').fadeIn(0);
      this.$('#authbutton').fadeOut(0);

      this.$('#fbextendbutton').fadeIn(0);
      this.$('#fbvalidationbutton').fadeOut(0);
    }

    // No
    if (ownApp === '0') {
      this.$('#own-app-testing').fadeIn();
      this.$('#own-app-details').fadeOut();

      this.$('#app_id').removeClass('required');
      this.$('#secret').removeClass('required');
      this.$('#canvas_page').removeClass('required');

      // Yes, with Canvas Page
    } else if (ownApp === '1') {
      this.$('#own-app-testing').fadeOut();
      this.$('#own-app-details-canvas-page').fadeIn(0);
      this.$('#own-app-details').fadeIn();

      this.$('#app_id').addClass('required');
      this.$('#secret').addClass('required');
      this.$('#canvas_page').addClass('required');
      // Yes (no Canvas Page)
    } else {
      this.$('#own-app-testing').fadeOut();
      this.$('#own-app-details-canvas-page').fadeOut(0);
      this.$('#own-app-details').fadeIn();

      this.$('#app_id').addClass('required');
      this.$('#secret').addClass('required');
      this.$('#canvas_page').removeClass('required');
    }
  },

  onChangeOgFeatures: function () {
    var ogFeatures = this.$('#og_features').val();

    if (ogFeatures === '1') {
      this.$('#og-fields').fadeIn();
    } else {
      this.$('#og-fields').fadeOut();
    }
  },

  onCheckFbApp: function () {
    var view = this;
    var fbAppDef = view.$('#fbAppDef');
    var fbAsyncInitButton = view.$('#fbAsyncInitButton');
    var fbChannelTab = view.$('#fbChannelTab');
    var appId = view.$('#app_id');
    var secret = view.$('#secret');

    var id = appId.val();
    var s = secret.val();

    if ((!_.isEmpty(id)) && (!_.isEmpty(s)) && (s.length > 30)) {
      fbAsyncInitButton.removeClass('disabled');
      fbChannelTab.removeClass('disabled');
    }

    if (fbAsyncInitButton.hasClass('disabled')) {
      setTimeout(function() {
        fbAppDef.tab('show');
      }, 100);

      return false;
    }

    return true;
  },

  onFbAsyncInit: function () {
    var view = this;

    if (!view.onCheckFbApp()) return;

    var params = appParamsHelper.get(this);
    var fbScope = view.$('#fb_api_perms_groups').val() === '1' ?
      view.$('#fb_api_perms_groups_detail').val() :
      view.$('#fb_api_perms').val();

    view.$('#fbLoginButton').attr('scope', fbScope);
    window.fbValidationView = view;

    window.fbAsyncInit = function() {
      FB.init({
        appId: params.p_app_id,
        cookie: true,
        xfbml: true,
        version: 'v2.8',
      });

      FB.getLoginStatus(function(response) {
        view.fbStatusChangeCallback(response);
      });
    };

    (function(d, s, id) {
      var js, fjs = d.getElementsByTagName(s)[0];
      if (d.getElementById(id)) return;
      js = d.createElement(s); js.id = id;
      js.src = "https://connect.facebook.net/en_US/sdk.js";
      fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));
  },

  fbStatusChangeCallback: function (response) {
    var view = this;

    if (response.status === 'connected') {
      FB.api('/me', function(response) {
        var authResponse = FB.getAuthResponse();

        document.getElementById('fbStatus').innerHTML = 'Thanks for logging in, ' + response.name + '!';
        view.$('#access_token').val(authResponse.accessToken);
      });
    } else {
      document.getElementById('fbStatus').innerHTML = 'Please log into this app.';
    }
  },

  onValidationReq: function () {
    var view = this;
    var params = appParamsHelper.get(view);

    this.collection.create(this.collection.model, {
      attrs: {
        own_app: params.p_own_app,
        app_id: params.p_app_id,
        secret: params.p_secret,
        access_token: params.p_access_token,
        token: params.p_token,
      },

      wait: true,
      dataType: 'text',
      error: function (model, fail) {
        validationHelper.showError(view, fail.responseText);
      }
    });
  },

  loadvalidation: function (resp) {
    var status = resp.get('status');
    var errorMessage = resp.get('message');
    var user;
    var tokenInfo;
    var issuedAt;
    var expiresAt;

    if (status) {
      user = resp.get('user');
      tokenInfo = resp.get('tokenInfo');
      issuedAt = tokenInfo.issued_at;
      expiresAt = tokenInfo.expires_at;

      validationHelper.showSuccess(this, user.id);

      this.$('#issued_at').val(issuedAt);
      this.$('#expires_at').val(expiresAt);

      this.attributes.dispatcher.trigger('fbapp:channelschanged');
    } else {
      validationHelper.showError(this, errorMessage);
    }
  },
});
