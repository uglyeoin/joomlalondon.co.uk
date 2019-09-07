/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* global angular, _, define, Backbone, FB, alert */

'use strict';

var UserChannelHelper = {
  onAuthMailchannel: function (params) {
    this.msg = null;
    this.authParams = params;

    return true;
  },

  onAuthFbchannel: function (params, view) {
    this.msg = null;
    this.authParams = params;
    this.pendingView = view;

    if (window.fbAsyncInit) {
      this._fbAssignToken();
    } else {
      this._fbInit();
    }

    return null;
  },

  _fbInit: function () {
    window.fbAsyncInit = function () {

      // init the FB JS SDK
      FB.init({
        appId: UserChannelHelper.authParams.app_id,
        xfbml: true,
        cookie: true,
        version: 'v2.8'
      });

      UserChannelHelper._fbAssignToken();
    };

    // Load the SDK asynchronously
    (function (d, s, id) {
      var js, fjs = d.getElementsByTagName(s)[0];
      if (d.getElementById(id)) {
        return;
      }
      js = d.createElement(s);
      js.id = id;

      js.src = "//connect.facebook.com/en_US/sdk.js";

      fjs.parentNode.insertBefore(js, fjs);
    } (document, 'script', 'facebook-jssdk'));
  },

  _fbAssignToken: function () {
    FB.login(
      function (response) {
        if (response.authResponse) {
          FB.getLoginStatus(
            function (response) {
              if (response.status === 'connected') {
                UserChannelHelper.authParams.access_token = response.authResponse.accessToken;
                UserChannelHelper.pendingView.authorizedChannel();
              } else {
                UserChannelHelper.onAuthFbchannel(UserChannelHelper.authParams, UserChannelHelper.pendingView);
              }
            }
            );
        } else {
          alert('User cancelled login or did not fully authorize.');
        }
      },
      {
        scope: 'public_profile,publish_actions'
      });
  },

  onAuthTwchannel: function (params) {
    this.msg = null;
    window.location = params.request_token_url;
  },

  onAuthLichannel: function (params) {
    this.msg = null;
    window.location = params.request_token_url;
  },

  onAuthLioauth2channel: function (params) {
    this.msg = null;
    window.location = params.request_token_url;
  },

  onAuthLigroupchannel: function (params) {
    this.msg = null;
    window.location = params.request_token_url;
  },

  getStatusMessage: function () {
    return this.msg;
  },

  getAuthParams: function () {
    return this.authParams;
  },

  showError: function (view, responseText) {
    if (responseText) {
      view.$('#alert-msg').removeClass('hide');
      view.$('#error-msg').html(responseText);
    } else {
      view.$('#alert-msg').addClass('hide');
    }
  }
};

