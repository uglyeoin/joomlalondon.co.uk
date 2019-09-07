/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* global jQuery, Request, Joomla, alert, Backbone, autotweet_canvas_app_url */

'use strict';

var appParamsHelper = {
  get: function (scope) {
    var canvas_page = null, // autotweet_canvas_app_url
      app_id = 'My-App-ID',
      secret = 'My-App-Secret',
      access_token = 'My-Access-Token',
      ownApp = scope.$('#use_own_api').val(),
      token = scope.$('#XTtoken').attr('name'),
      channelTypeId = scope.$('#channeltype_id').val();

    // Yes, with Canvas Page
    if (ownApp === '1') {
      canvas_page = scope.$('#canvas_page').val().trim();
      app_id = scope.$('#app_id').val().trim();
      secret = scope.$('#secret').val().trim();

      scope.$('#canvas_page').val(canvas_page);
      scope.$('#app_id').val(app_id);
      scope.$('#secret').val(secret);

      // Yes (no Canvas Page)
    } else if (ownApp === '2') {
      app_id = scope.$('#app_id').val().trim();
      secret = scope.$('#secret').val().trim();

      scope.$('#app_id').val(app_id);
      scope.$('#secret').val(secret);
    }

    access_token = scope.$('#access_token').val().trim();
    scope.$('#access_token').val(access_token);

    var params = {
      p_own_app: ownApp,
      p_canvas_page: canvas_page,
      p_encoded_canvas_page: encodeURIComponent(canvas_page),
      p_app_id: encodeURIComponent(app_id),
      p_secret: encodeURIComponent(secret),
      p_access_token: encodeURIComponent(access_token),
      p_token: encodeURIComponent(token),
      p_channelTypeId: channelTypeId
    };

    var url_params =
      'app_id=' + params.p_app_id
      + '&secret=' + params.p_secret
      + '&access_token=' + params.p_access_token
      + '&ownapp=' + params.p_own_app
      + '&canvas_page=' + params.p_encoded_canvas_page
      + '&token=' + params.p_token;

    params.p_url_params = url_params;

    return params;
  },

  getLi: function (scope) {
    var api_key = scope.$('#api_key').val(),
      secret_key = scope.$('#secret_key').val(),
      oauth_user_token = scope.$('#oauth_user_token').val(),
      oauth_user_secret = scope.$('#oauth_user_secret').val(),
      token = scope.$('#XTtoken').attr('name');

    var params = {
      p_api_key: api_key,
      p_secret_key: encodeURIComponent(secret_key),
      p_oauth_user_token: encodeURIComponent(oauth_user_token),
      p_oauth_user_secret: encodeURIComponent(oauth_user_secret),
      p_token: encodeURIComponent(token)
    };

    var url_params =
				  'api_key=' + params.p_api_key
      + '&secret_key=' + params.p_secret_key
      + '&oauth_user_token=' + params.p_oauth_user_token
      + '&oauth_user_secret=' + params.p_oauth_user_secret
      + '&token=' + params.p_token;

    params.p_url_params = url_params;

    return params;
  }
};

var validationHelper = {
  showSuccess: function (scope, userId, socialIcon, socialUrl) {
    scope.$('#user_id').val(userId);

    this.assignSocialUrl(scope, 'social_url', socialIcon, socialUrl);

    scope.$('#validation-notchecked').hide();
    scope.$('#validation-error').hide();
    scope.$('#validation-errormsg').hide();

    scope.$('#validation-success').show();
  },

  assignSocialUrl: function (scope, target, socialIcon, socialUrl) {
    if (socialUrl) {
      scope.$('#' + target).val(socialUrl);
      scope.$('.' + target).html(this.formatUrl(socialIcon, socialUrl));
    }
  },

  showError: function (scope, msg) {
    scope.$('#user_id').val('');

    scope.$('#validation-notchecked').hide();
    scope.$('#validation-success').hide();

    scope.$('#validation-theerrormsg').html(msg);
    scope.$('#validation-error').show();
    scope.$('#validation-errormsg').show();
  },

  formatUrl: function (socialIcon, socialUrl) {
    return '<p><a href="' + socialUrl + '" target="_blank">' + socialIcon + ' ' + socialUrl + '</a></p>';
  }
};

