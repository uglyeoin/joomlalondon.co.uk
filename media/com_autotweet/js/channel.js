/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* global jQuery, Request, Joomla, alert, Backbone, define, _ */

define('channel', ['extlycore'], function (Core) {
  "use strict";

  /* BEGIN - variables to be inserted here */


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

/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone, Core */

var BloggerValidation = Core.ExtlyModel.extend({
  url: function () {
				return Core.SefHelper.route('index.php?option=com_autotweet&view=channels&task=getBloggerValidation');
  }
});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var Channel = Core.ExtlyModel.extend({
  url: function () {
				return Core.SefHelper.route('index.php?option=com_autotweet&view=channels&task=getParamsForm&toolbar=none');
  }
});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var FbAlbum = Core.ExtlyModel.extend({
  url: function () {
				return Core.SefHelper.route('index.php?option=com_autotweet&view=channels&task=getFbAlbums');
  }
});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var FbChannel = Core.ExtlyModel.extend({
  url: function () {
				return Core.SefHelper.route('index.php?option=com_autotweet&view=channels&task=getFbChannels');
  }
});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var FbChValidation = Core.ExtlyModel.extend({
  url: function () {
				return Core.SefHelper.route('index.php?option=com_autotweet&view=channels&task=getFbChValidation');
  }
});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var FbExtend = Core.ExtlyModel.extend({
  url: function () {
				return Core.SefHelper.route('index.php?option=com_autotweet&view=channels&task=getFbExtend');
  }
});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var FbValidation = Core.ExtlyModel.extend({
			url : function() {
				return Core.SefHelper.route('index.php?option=com_autotweet&view=channels&task=getFbValidation');
			}
		});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var GplusValidation = Core.ExtlyModel.extend({
  url: function () {
				return Core.SefHelper.route('index.php?option=com_autotweet&view=channels&task=getGplusValidation');
  }
});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var LiGroup = Core.ExtlyModel.extend({
  url: function () {
				return Core.SefHelper.route('index.php?option=com_autotweet&view=channels&task=getLiGroups');
  }
});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var LiOAuth2Company = Core.ExtlyModel.extend({
			url : function() {
				return Core.SefHelper.route('index.php?option=com_autotweet&view=channels&task=getLiOAuth2Companies');
			}
		});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var LiOAuth2Validation = Core.ExtlyModel.extend({
  url: function () {
				return Core.SefHelper.route('index.php?option=com_autotweet&view=channels&task=getLiOAuth2Validation');
  }
});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var MediumValidation = Core.ExtlyModel.extend({
  url: function () {
				return Core.SefHelper.route('index.php?option=com_autotweet&view=channels&task=getMediumValidation');
  }
});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var OneSignalValidation = Core.ExtlyModel.extend({
  url: function () {
				return Core.SefHelper.route('index.php?option=com_autotweet&view=channels&task=getOneSignalValidation');
  }
});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone, Core */

var PageSpeedValidation = Core.ExtlyModel.extend({
  url: function () {
				return Core.SefHelper.route('index.php?option=com_autotweet&view=channels&task=getPageSpeedValidation');
  }
});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone, Core */

var PinterestValidation = Core.ExtlyModel.extend({
  url: function () {
				return Core.SefHelper.route('index.php?option=com_autotweet&view=channels&task=getPinterestValidation');
  }
});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var PushAlertValidation = Core.ExtlyModel.extend({
  url: function () {
				return Core.SefHelper.route('index.php?option=com_autotweet&view=channels&task=getPushAlertValidation');
  }
});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var PushwooshValidation = Core.ExtlyModel.extend({
  url: function () {
				return Core.SefHelper.route('index.php?option=com_autotweet&view=channels&task=getPushwooshValidation');
  }
});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var ScoopitTopic = Core.ExtlyModel.extend({
  url: function () {
				return Core.SefHelper.route('index.php?option=com_autotweet&view=channels&task=getScoopitTopics');
  }
});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var ScoopitValidation = Core.ExtlyModel.extend({
  url: function () {
				return Core.SefHelper.route('index.php?option=com_autotweet&view=channels&task=getScoopitValidation');
  }
});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var TelegramValidation = Core.ExtlyModel.extend({
  url: function () {
				return Core.SefHelper.route('index.php?option=com_autotweet&view=channels&task=getTelegramValidation');
  }
});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var TumblrValidation = Core.ExtlyModel.extend({
  url: function () {
				return Core.SefHelper.route('index.php?option=com_autotweet&view=channels&task=getTumblrValidation');
  }
});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var TwValidation = Core.ExtlyModel.extend({
  url: function () {
				return Core.SefHelper.route('index.php?option=com_autotweet&view=channels&task=getTwValidation');
  }
});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var VkGroup = Core.ExtlyModel.extend({
  url: function () {
				return Core.SefHelper.route('index.php?option=com_autotweet&view=channels&task=getVkGroups');
  }
});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var VkValidation = Core.ExtlyModel.extend({
  url: function () {
				return Core.SefHelper.route('index.php?option=com_autotweet&view=channels&task=getVkValidation');
  }
});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var XingValidation = Core.ExtlyModel.extend({
  url: function () {
				return Core.SefHelper.route('index.php?option=com_autotweet&view=channels&task=getXingValidation');
  }
});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var BloggerValidations = Backbone.Collection.extend({
  model: BloggerValidation
});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var Channels = Backbone.Collection.extend({
  model: Channel
});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var FbAlbums = Backbone.Collection.extend({
  model: FbAlbum
});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var FbChannels = Backbone.Collection.extend({
  model: FbChannel
});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var FbChValidations = Backbone.Collection.extend({
  model: FbChValidation
});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var FbExtends = Backbone.Collection.extend({
  model: FbExtend
});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var FbValidations = Backbone.Collection.extend({
  model: FbValidation
});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var GplusValidations = Backbone.Collection.extend({
  model: GplusValidation
});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var LiGroups = Backbone.Collection.extend({
  model: LiGroup
});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var LiOAuth2Companies = Backbone.Collection.extend({
  model: LiOAuth2Company
});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var LiOAuth2Validations = Backbone.Collection.extend({
  model: LiOAuth2Validation
});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var MediumValidations = Backbone.Collection.extend({
  model: MediumValidation
});

/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var OneSignalValidations = Backbone.Collection.extend({
  model: OneSignalValidation
});

/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var PageSpeedValidations = Backbone.Collection.extend({
  model: PageSpeedValidation
});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var PinterestValidations = Backbone.Collection.extend({
  model: PinterestValidation
});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var PushAlertValidations = Backbone.Collection.extend({
  model: PushAlertValidation
});

/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var PushwooshValidations = Backbone.Collection.extend({
  model: PushwooshValidation
});

/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var ScoopitTopics = Backbone.Collection.extend({
  model: ScoopitTopic
});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var ScoopitValidations = Backbone.Collection.extend({
  model: ScoopitValidation
});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var TelegramValidations = Backbone.Collection.extend({
  model: TelegramValidation
});

/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var TumblrValidations = Backbone.Collection.extend({
  model: TumblrValidation
});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var TwValidations = Backbone.Collection.extend({
  model: TwValidation
});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var VkGroups = Backbone.Collection.extend({
  model: VkGroup
});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var VkValidations = Backbone.Collection.extend({
  model: VkValidation
});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

var XingValidations = Backbone.Collection.extend({
  model: XingValidation
});
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

var BloggerValidationView = Backbone.View.extend({
  events: {
    'click #bloggervalidationbutton': 'onValidationReq'
  },

  initialize: function () {
    this.collection.on('add', this.loadvalidation, this);
  },

  onValidationReq: function () {
    var view = this,

      channelId = view.$('#channel_id').val().trim(),
      clientSecret = view.$('#client_secret').val().trim(),
      developerKey = view.$('#developer_key').val().trim(),

      token = view.$('#XTtoken').attr('name');

    view.$('#channel_id').val(channelId);
    view.$('#client_secret').val(clientSecret);
    view.$('#developer_key').val(developerKey);

    view.$(".loaderspinner").addClass('loading');

    this.collection.create(this.collection.model, {
      attrs: {
        channel_id: channelId,
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
      errorMessage = resp.get('message'),
      user = resp.get('user'),
      socialIcon = resp.get('social_icon'),
      socialUrl = resp.get('social_url');

    this.$(".loaderspinner").removeClass('loading');

    if (status) {
      validationHelper.showSuccess(this, user.id, socialIcon, socialUrl);
    } else {
      validationHelper.showError(this, errorMessage);
    }
  }

});
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
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* global jQuery, Request, Joomla, alert, _, Backbone, validationHelper, appParamsHelper, Core, messagesview */

"use strict";

var FbAlbumView = Backbone.View
		.extend({

    events: {
      'click #fbalbumloadbutton': 'onAlbumsReq'
    },

    initialize: function () {
      this.collection.on('add', this.loadFbAlbum, this);
      this.fbalbumlist = '#xtformfbalbum_id';
    },

    onAlbumsReq: function onAlbumsReq() {
      var thisView = this,
        params = appParamsHelper.get(thisView),
        list = thisView.$(this.fbalbumlist),
        fbChannelView = this.attributes.fbChannelView,
        channelId = fbChannelView.getFbChannelId(),
        channelToken = fbChannelView.getFbChannelAccessToken();

      Core.UiHelper.listReset(list);

      this.collection.create(this.collection.model, {
        attrs: {
          own_app: params.p_own_app,
          app_id: params.p_app_id,
          secret: params.p_secret,
          access_token: params.p_access_token,
          channel_id: channelId,
          channel_access_token: channelToken,
          token: params.p_token
        },

        wait: true,
        dataType: 'text',
        error: function (model, fail, xhr) {
          validationHelper.showError(messagesview,
            fail.responseText);
        }
      });
    },

    loadFbAlbum: function loadFbAlbum(message) {
      var fbalbumlist = this.$(this.fbalbumlist), albums;

      fbalbumlist.empty();
      if (message.get('status')) {
        albums = message.get('albums');
        _.each(albums, function (album) {
          var opt = new Option();
          opt.value = album.id;
          opt.text = album.name;
          fbalbumlist.append(opt);
        });
        fbalbumlist.trigger('liszt:updated');
      } else {
        validationHelper.showError(this.attributes.messagesview,
          message.get('message'));
      }

    }

		});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* global jQuery, Request, Joomla, alert, _, Backbone, validationHelper, appParamsHelper, Core, messagesview */

"use strict";

var FbChannelView = Backbone.View
		.extend({

    events: {
      'change #xtformfbchannel_id': 'onChangeChannel'
    },

    initialize: function () {
      this.attributes.dispatcher.on('fbapp:channelschanged',
        this.onAccessTokenChanged, this);
      this.collection.on('add', this.loadFbChannel, this);

      this.fbchannellist = '#xtformfbchannel_id';
      this.fbChannelSelected = null;

      // this.$('.group-warn').fadeOut();
    },

    onAccessTokenChanged: function () {
      var thisView = this, messagesview = this.attributes.messagesview, params = appParamsHelper
        .get(thisView);

      Core.UiHelper.listReset(thisView.$(this.fbchannellist));

      this.collection.create(this.collection.model, {
        attrs: {
          own_app: params.p_own_app,
          app_id: params.p_app_id,
          secret: params.p_secret,
          access_token: params.p_access_token,
          token: params.p_token,
          channeltype_id: params.p_channelTypeId
        },

        wait: true,
        dataType: 'text',
        error: function (model, fail, xhr) {
          validationHelper.showError(messagesview,
            fail.responseText);
        }
      });
    },

    onChangeChannel: function () {
      var accessToken,
        channelType,
        oselected,
        socialIcon,
        socialUrl,
        xtformshowUrl;

      xtformshowUrl = this.$('#xtformshow_url');
      xtformshowUrl.val('off');
      xtformshowUrl.trigger('liszt:updated');

      this.fbChannelSelected = null;
      accessToken = this.getFbChannelAccessToken();
      channelType = this.getFbChannelType();
      oselected = this.getFbChannelSelected();
      socialIcon = oselected.attr('social_icon');
      socialUrl = oselected.attr('social_url');

      this.$('#fbchannel_access_token').val(accessToken);
      validationHelper.assignSocialUrl(this, 'social_url', socialIcon, socialUrl);

      /*
      if (channelType === 'Group') {
        this.$('.group-warn').fadeIn();
      } else {
        this.$('.group-warn').fadeOut();
      }
      */

      if (channelType === 'User') {
        this.$('.open_graph_features').fadeIn();
      } else {
        this.$('.open_graph_features').fadeOut();
      }

      this.$('.channel-type').val(channelType);
    },

    getFbChannelSelected: function () {
      if (!this.fbChannelSelected) {
        this.fbChannelSelected = this.$(this.fbchannellist + ' option:selected');
      }

      return this.fbChannelSelected;
    },

    getFbChannelAccessToken: function () {
      var oselected = this.getFbChannelSelected(),
        access_token = 'INVALID';
      if (oselected) {
        access_token = oselected.attr('access_token');
      }
      return access_token;
    },

    getFbChannelType: function () {
      var oselected = this.getFbChannelSelected(),
        channelType = 'INVALID';

      if (oselected) {
        channelType = oselected.attr('data_type');
      }

      return channelType;
    },

    getFbChannelId: function () {
      return this.getFbChannelSelected().val();
    },

    loadFbChannel: function (message) {
      var fbchannellist = this.$(this.fbchannellist), channels, socialIcon, first = true;

      fbchannellist.empty();
      this.fbChannelSelected = null;

      if (message.get('status')) {
        channels = message.get('channels');
        socialIcon = message.get('icon');

        _.each(channels, function (channel) {
          var opt = new Option();
          opt.value = channel.id;
          opt.text = channel.type + ': ' + channel.name;

          jQuery(opt)
            .attr('access_token', channel.access_token)
            .attr('data_type', channel.type)
            .attr('social_icon', socialIcon)
            .attr('social_url', channel.url);

          if (first) {
            first = false;
            opt.selected = true;
          }

          fbchannellist.append(opt);
        });

        this.onChangeChannel();

        fbchannellist.trigger('liszt:updated');
      } else {
        validationHelper.showError(this.attributes.messagesview,
          message.get('message'));
      }

    }

		});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* global jQuery, Request, Joomla, alert, _, Backbone, validationHelper, appParamsHelper, Core, messagesview */

"use strict";

var FbChValidationView = Backbone.View.extend({
  events: {
    'click #fbchvalidationbutton': 'onValidationReq'
  },

  initialize: function () {
    var view = this;

    this.collection.on('add', this.loadvalidation, this);

    this.$el.ajaxStart(function () {
      view.$(".loaderspinner72").addClass('loading72');
    }).ajaxStop(function () {
      view.$(".loaderspinner72").removeClass('loading72');
    });
  },

  onValidationReq: function () {
    var view = this,
      params = appParamsHelper.get(view),
      fbchannel_access_token = this.$('#fbchannel_access_token').val();

    this.collection.create(this.collection.model, {
      attrs: {
        own_app: params.p_own_app,
        app_id: params.p_app_id,
        secret: params.p_secret,
        access_token: params.p_access_token,
        token: params.p_token,
        fbchannel_access_token: fbchannel_access_token
      },

      wait: true,
      dataType: 'text',
      error: function (model, fail, xhr) {
        validationHelper.showError(view, fail.responseText);
      }
    });
  },

  loadvalidation: function (resp) {
    var status = resp.get('status'),
      error_message = resp.get('message'),
      tokenInfo = resp.get('tokenInfo'),
      issued_at = tokenInfo.issued_at,
      expires_at = tokenInfo.expires_at;

    if (status) {
      this.$('#channel_issued_at').val(issued_at);
      this.$('#channel_expires_at').val(expires_at);
    } else {
      this.$('#channel_issued_at').val(error_message);
      this.$('#channel_expires_at').val('');
    }
  }

});
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* global jQuery, Request, Joomla, alert, _, Backbone, validationHelper, appParamsHelper, Core, messagesview */

"use strict";

var FbExtendView = Backbone.View.extend({
  events: {
    'click #fbextendbutton': 'onExtendReq'
  },

  initialize: function () {
    var view = this;

    this.collection.on('add', this.loadExtend, this);

    this.$el.ajaxStart(function () {
      view.$(".loaderspinner72").addClass('loading72');
    }).ajaxStop(function () {
      view.$(".loaderspinner72").removeClass('loading72');
    });
  },

  onExtendReq: function () {
    var view = this, params = appParamsHelper.get(view);

    this.collection.create(this.collection.model, {
      attrs: {
        own_app: params.p_own_app,
        app_id: params.p_app_id,
        secret: params.p_secret,
        access_token: params.p_access_token,
        token: params.p_token
      },

      wait: true,
      dataType: 'text',
      error: function (model, fail, xhr) {
        validationHelper.showError(view, fail.responseText);
      }
    });
  },

  loadExtend: function (resp) {
    var status = resp.get('status'),
      error_message = resp.get('message'),
      user,
      extended_token,
      tokenInfo,
      issued_at,
      expires_at;

    if (status) {
      user = resp.get('user');
      extended_token = resp.get('extended_token');
      tokenInfo = resp.get('tokenInfo');
      issued_at = tokenInfo.issued_at;
      expires_at = tokenInfo.expires_at;

      if (user) {
        validationHelper.showSuccess(this, user.id);
      } else {
        validationHelper.showSuccess(this, tokenInfo.data.user_id);
      }

      this.$('#access_token').val(extended_token);
      this.$('#issued_at').val(issued_at);
      this.$('#expires_at').val(expires_at);

      this.attributes.dispatcher.trigger("fbapp:channelschanged");
    } else {
      validationHelper.showError(this, error_message);
    }
  }

});
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

var GplusValidationView = Backbone.View.extend({
  events: {
    'click #gplusvalidationbutton': 'onValidationReq'
  },

  initialize: function () {
    this.collection.on('add', this.loadvalidation, this);
  },

  onValidationReq: function () {
    var view = this,

      channel_id = view.$('#channel_id').val().trim(),
      client_secret = view.$('#client_secret').val().trim(),
      developer_key = view.$('#developer_key').val().trim(),

      token = view.$('#XTtoken').attr('name');

    view.$('#channel_id').val(channel_id);
    view.$('#client_secret').val(client_secret);
    view.$('#developer_key').val(developer_key);

    view.$(".loaderspinner").addClass('loading');

    this.collection.create(this.collection.model, {
      attrs: {
        channel_id: channel_id,
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
      error_message = resp.get('message'),
      user = resp.get('user'),
      socialIcon = resp.get('social_icon'),
      socialUrl = resp.get('social_url');

    this.$(".loaderspinner").removeClass('loading');

    if (status) {
      validationHelper.showSuccess(this, user.id, socialIcon, socialUrl);
    } else {
      validationHelper.showError(this, error_message);
    }
  }

});
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

var LiGroupView = Backbone.View
		.extend({

    events: {
      'click #ligrouploadbutton': 'onChangeChannel',
      'change #xtformgroup_id': 'onChangeGroup'
    },

    initialize: function () {
      this.collection.on('add', this.loadLiGroup, this);
      this.ligrouplist = '#xtformgroup_id';
      this.$('.group-warn').fadeOut();
    },

    onChangeChannel: function () {
      var thisView = this,
        params = appParamsHelper.getLi(thisView);

      Core.UiHelper.listReset(thisView.$(this.ligrouplist));

      this.collection.create(this.collection.model, {
        attrs: {
          api_key: params.p_api_key,
          secret_key: params.p_secret_key,
          oauth_user_token: params.p_oauth_user_token,
          oauth_user_secret: params.p_oauth_user_secret,
          token: params.p_token
        },

        wait: true,
        dataType: 'text',
        error: function (model, fail, xhr) {
          validationHelper.showError(this, fail.responseText);
        }
      });
    },

    loadLiGroup: function (message) {
      var ligrouplist = this.$(this.ligrouplist), channels, socialIcon, first = true;

      ligrouplist.empty();
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

          ligrouplist.append(opt);
        });

        this.onChangeGroup();
        validationHelper.showSuccess(this, '');

        ligrouplist.trigger('liszt:updated');
      } else {
        validationHelper.showError(this, message.get('message'));
      }

    },

    onChangeGroup: function () {
      var oselected = this.$('#xtformgroup_id option:selected'),
        socialIcon = oselected.attr('social_icon'),
        socialUrl = oselected.attr('social_url');

      validationHelper.assignSocialUrl(this, 'social_url_ligroup', socialIcon, socialUrl);
    }
		});
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

var LiOAuth2ValidationView = Backbone.View.extend({
  events: {
    'click #lioauth2validationbutton': 'onValidationReq'
  },

  initialize: function () {
    this.collection.on('add', this.loadvalidation, this);
  },

  onValidationReq: function onValidationReq() {
    var view = this,
      channel_id = view.$('#channel_id').val().trim(),
      token = view.$('#XTtoken').attr('name');

    view.$('#channel_id').val(channel_id);
    view.$(".loaderspinner").addClass('loading');

    this.collection.create(this.collection.model, {
      attrs: {
        channel_id: channel_id,
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
      error_message = resp.get('message'),
      user = resp.get('user'),
      icon = resp.get('icon'),
      url = resp.get('url');

    this.$(".loaderspinner").removeClass('loading');

    if (status) {
      validationHelper.showSuccess(this, user.id, icon, url);
    } else {
      validationHelper.showError(this, error_message);
    }
  }

});
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

var MediumValidationView = Backbone.View.extend({
  events: {
    'click #mediumvalidationbutton': 'onValidationReq'
  },

  initialize: function () {
    this.collection.on('add', this.loadvalidation, this);
  },

  onValidationReq: function onValidationReq() {
    var view = this,

      botToken = view.$('#integration_token').val().trim(),

      token = view.$('#XTtoken').attr('name');

    view.$('#integration_token').val(botToken);

    view.$(".loaderspinner").addClass('loading');

    this.collection.create(this.collection.model, {
      attrs: {
        integration_token: botToken,
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
      errorMessage = resp.get('message'),
      user = resp.get('user'),
      icon = resp.get('icon'),
      url = resp.get('url');

    this.$(".loaderspinner").removeClass('loading');

    if (status) {
      validationHelper.showSuccess(this, user, icon, url);
    } else {
      validationHelper.showError(this, errorMessage);
    }
  }

});
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

var PageSpeedValidationView = Backbone.View.extend({
  events: {
    'click #pagespeedvalidationbutton': 'onValidationReq'
  },

  initialize: function () {
    this.collection.on('add', this.loadvalidation, this);
  },

  onValidationReq: function () {
    var view = this,

      channelId = view.$('#channel_id').val().trim(),
      apiKey = view.$('#api_key').val().trim(),

      token = view.$('#XTtoken').attr('name');

    view.$('#channel_id').val(channelId);
    view.$('#api_key').val(apiKey);

    view.$(".loaderspinner").addClass('loading');

    this.collection.create(this.collection.model, {
      attrs: {
        channel_id: channelId,
        api_key: apiKey,
        token: token,
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
      validationHelper.showSuccess(this, null, null, null);
    } else {
      validationHelper.showError(this, errorMessage);
    }
  }

});
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

var PinterestValidationView = Backbone.View.extend({
  events: {
    'click #pinterestvalidationbutton': 'onValidationReq'
  },

  initialize: function () {
    this.collection.on('add', this.loadvalidation, this);
  },

  onValidationReq: function () {
    var view = this,

      channelId = view.$('#channel_id').val().trim(),
      appId = view.$('#app_id').val().trim(),
      appSecret = view.$('#app_secret').val().trim(),
      token = view.$('#XTtoken').attr('name');

    view.$('#channel_id').val(channelId);
    view.$('#app_id').val(appId);
    view.$('#app_secret').val(appSecret);

    view.$(".loaderspinner").addClass('loading');

    this.collection.create(this.collection.model, {
      attrs: {
        channel_id: channelId,
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
      errorMessage = resp.get('message'),
      user = resp.get('user'),
      socialIcon = resp.get('social_icon'),
      socialUrl = resp.get('social_url');

    this.$(".loaderspinner").removeClass('loading');

    if (status) {
      validationHelper.showSuccess(this, user.id, socialIcon, socialUrl);
    } else {
      validationHelper.showError(this, errorMessage);
    }
  }

});
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

var ScoopitValidationView = Backbone.View.extend({
  events: {
    'click #scoopitvalidationbutton': 'onValidationReq'
  },

  initialize: function () {
    this.collection.on('add', this.loadvalidation, this);
  },

  onValidationReq: function onValidationReq() {
    var view = this,

      channel_id = view.$('#channel_id').val().trim(),
      consumer_key = view.$('#consumer_key').val().trim(),
      consumer_secret = view.$('#consumer_secret').val().trim(),
      access_token = view.$('#access_token').val().trim(),
      access_secret = view.$('#access_secret').val().trim(),

      token = view.$('#XTtoken').attr('name');

    view.$('#channel_id').val(channel_id);
    view.$('#consumer_key').val(consumer_key);
    view.$('#consumer_secret').val(consumer_secret);
    view.$('#access_token').val(access_token);
    view.$('#access_secret').val(access_secret);

    view.$(".loaderspinner").addClass('loading');

    this.collection.create(this.collection.model, {
      attrs: {
        channel_id: channel_id,
        consumer_key: consumer_key,
        consumer_secret: consumer_secret,
        access_token: access_token,
        access_secret: access_secret,
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
      error_message = resp.get('message'),
      user = resp.get('user'),
      icon = resp.get('icon'),
      url = resp.get('url');

    this.$(".loaderspinner").removeClass('loading');

    if (status) {
      validationHelper.showSuccess(this, user.id_str, icon, url);
    } else {
      validationHelper.showError(this, error_message);
    }
  }

});
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

var TelegramValidationView = Backbone.View.extend({
  events: {
    'click #telegramvalidationbutton': 'onValidationReq'
  },

  initialize: function () {
    this.collection.on('add', this.loadvalidation, this);
  },

  onValidationReq: function onValidationReq() {
    var view = this,

      botToken = view.$('#bot_token').val().trim(),
      chatId = view.$('#chat_id').val().trim(),

      token = view.$('#XTtoken').attr('name');

    view.$('#bot_token').val(botToken);
    view.$('#chat_id').val(chatId);

    view.$(".loaderspinner").addClass('loading');

    this.collection.create(this.collection.model, {
      attrs: {
        bot_token: botToken,
        chat_id: chatId,
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
      errorMessage = resp.get('message'),
      user = resp.get('user'),
      icon = resp.get('icon'),
      url = resp.get('url');

    this.$(".loaderspinner").removeClass('loading');

    if (status) {
      validationHelper.showSuccess(this, user, icon, url);
    } else {
      validationHelper.showError(this, errorMessage);
    }
  }

});
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

var TumblrValidationView = Backbone.View.extend({
  events: {
    'click #tumblrvalidationbutton': 'onValidationReq'
  },

  initialize: function () {
    this.collection.on('add', this.loadvalidation, this);
    this.bloglist = '#blogs';
  },

  onValidationReq: function onValidationReq() {
    var view = this,
      channel_id = view.$('#channel_id').val().trim(),
      token = view.$('#XTtoken').attr('name'),
      list = this.$(this.bloglist);

    view.$('#channel_id').val(channel_id);

    view.$(".loaderspinner").addClass('loading');
    Core.UiHelper.listReset(list);

    this.collection.create(this.collection.model, {
      attrs: {
        channel_id: channel_id,
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
      error_message = resp.get('message'),
      user = resp.get('user'),
      icon = resp.get('icon'),
      url = resp.get('url'),
      list = this.$(this.bloglist);

    this.$(".loaderspinner").removeClass('loading');

    if (status) {
      list.empty();
      _.each(user.blogs, function (item) {
        var opt = new Option();
        opt.value = item.id;
        opt.text = item.name;
        list.append(opt);
      });
      list.trigger('liszt:updated');

      validationHelper.showSuccess(this, user.name, icon, url);
    } else {
      validationHelper.showError(this, error_message);
    }
  }

});
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

var TwValidationView = Backbone.View.extend({
  events: {
    'click #twvalidationbutton': 'onValidationReq'
  },

  initialize: function () {
    this.collection.on('add', this.loadvalidation, this);
  },

  onValidationReq: function onValidationReq() {
    var view = this,

      consumer_key = view.$('#consumer_key').val().trim(),
      consumer_secret = view.$('#consumer_secret').val().trim(),
      access_token = view.$('#access_token').val().trim(),
      access_token_secret = view.$('#access_token_secret').val().trim(),

      token = view.$('#XTtoken').attr('name');

    view.$('#consumer_key').val(consumer_key);
    view.$('#consumer_secret').val(consumer_secret);
    view.$('#access_token').val(access_token);
    view.$('#access_token_secret').val(access_token_secret);

    view.$(".loaderspinner").addClass('loading');

    this.collection.create(this.collection.model, {
      attrs: {
        consumer_key: consumer_key,
        consumer_secret: consumer_secret,
        access_token: access_token,
        access_token_secret: access_token_secret,
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
      error_message = resp.get('message'),
      user = resp.get('user'),
      icon = resp.get('icon'),
      url = resp.get('url');

    this.$(".loaderspinner").removeClass('loading');

    if (status) {
      validationHelper.showSuccess(this, user.id_str, icon, url);
    } else {
      validationHelper.showError(this, error_message);
    }
  }

});
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

var VkGroupView = Backbone.View
		.extend({

    events: {
      'click #vkgrouploadbutton': 'ongroupsReq',
      'change #xtformvkgroup_id': 'onChangeGroup'
    },

    initialize: function () {
      this.collection.on('add', this.loadVkGroup, this);
      this.vkgrouplist = '#xtformvkgroup_id';
    },

    ongroupsReq: function () {
      var thisView = this,
        list = thisView.$(this.vkgrouplist),

        channelId = thisView.$('#channel_id').val().trim(),
        channelToken = thisView.$('#access_token').val().trim(),

        token = thisView.$('#XTtoken').attr('name');

      thisView.$('#channel_id').val(channelId);
      thisView.$('#access_token').val(channelToken);

      Core.UiHelper.listReset(list);

      this.collection.create(this.collection.model, {
        attrs: {
          channel_id: channelId,
          access_token: channelToken,
          token: token
        },

        wait: true,
        dataType: 'text',
        error: function (model, fail, xhr) {
          validationHelper.showError(thisView,
            fail.responseText);
        }
      });
    },

    loadVkGroup: function (message) {
      var vkgrouplist = this.$(this.vkgrouplist), groups, socialIcon, first = true;

      vkgrouplist.empty();
      if (message.get('status')) {
        groups = message.get('groups');
        socialIcon = message.get('social_icon');

        _.each(groups, function (group) {
          var opt = new Option();

          opt.value = group.gid;
          opt.text = group.name;

          jQuery(opt)
            .attr('social_icon', socialIcon)
            .attr('social_url', group.url);

          if (first) {
            first = false;
            opt.selected = true;
          }

          vkgrouplist.append(opt);
        });

        this.onChangeGroup();

        vkgrouplist.trigger('liszt:updated');
      } else {
        validationHelper.showError(this, message.get('message'));
      }

    },

    onChangeGroup: function () {
      var oselected = this.$('#xtformvkgroup_id option:selected'),
        socialIcon = oselected.attr('social_icon'),
        socialUrl = oselected.attr('social_url');

      validationHelper.assignSocialUrl(this, 'social_url_vkgroup', socialIcon, socialUrl);
    }

		});
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

var VkValidationView = Backbone.View.extend({
  events: {
    'click #authorizeButton': 'onAuthorization',
    'click #vkvalidationbutton': 'onValidationReq'
  },

  initialize: function () {
    this.collection.on('add', this.loadvalidation, this);
  },

  onAuthorization: function onAuthorization() {
    this.$('#authorizeGroup').addClass('hide');
    this.$('#validationGroup').removeClass('hide');
  },

  processTokenUrl: function processTokenUrl(view) {
    var hash, params, access_token = {};

    // Access token is coming

    hash = view.$('#token_url').val().trim();
    params = hash.split('#');

    if (_.size(params) == 2) {
      hash = params[1];
    } else {
      return false;
    }

    if (!_.isEmpty(hash)) {
      params = hash.split('&');
      _.each(params, function (param) {
        var kv = param.split('='), k, v;

        if (_.size(kv) == 2) {
          k = kv[0];
          v = kv[1];

          jQuery('#raw_' + k).val(v);

          access_token[k] = v;
        }
      }
      );

      jQuery('#access_token').val(JSON.stringify(access_token));

      return true;
    }

    return false;
  },

  onValidationReq: function onValidationReq() {
    var view = this,
      channel_id = view.$('#channel_id').val(),
      access_token,
      token = view.$('#XTtoken').attr('name');

    if (!this.processTokenUrl(view)) {
      validationHelper.showError(view, 'Invalid Token Url');
    }

    access_token = view.$('#access_token').val();

    view.$(".loaderspinner").addClass('loading');

    this.collection.create(this.collection.model, {
      attrs: {
        channel_id: channel_id,
        access_token: access_token,
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
      error_message = resp.get('message'),
      socialIcon = resp.get('social_icon'),
      socialUrl = resp.get('social_url');

    this.$(".loaderspinner").removeClass('loading');

    if (status) {
      validationHelper.showSuccess(this, error_message, socialIcon, socialUrl);
    } else {
      validationHelper.showError(this, error_message);
    }
  }

});
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

var XingValidationView = Backbone.View.extend({
  events: {
    'click #xingvalidationbutton': 'onValidationReq'
  },

  initialize: function () {
    this.collection.on('add', this.loadvalidation, this);
  },

  onValidationReq: function onValidationReq() {
    var view = this,
      channel_id = view.$('#channel_id').val().trim(),
      token = view.$('#XTtoken').attr('name');

    view.$('#channel_id').val(channel_id);
    view.$(".loaderspinner").addClass('loading');

    this.collection.create(this.collection.model, {
      attrs: {
        channel_id: channel_id,
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
      error_message = resp.get('message'),
      user = resp.get('user'),
      icon = resp.get('icon'),
      url = resp.get('url');

    this.$(".loaderspinner").removeClass('loading');

    if (status) {
      validationHelper.showSuccess(this, user, icon, url);
    } else {
      validationHelper.showError(this, error_message);
    }
  }

});
  /* END - variables to be inserted here */

  var $adminForm = jQuery('#adminForm');

  (new ChannelView({
    el: $adminForm,
    collection: new Channels()
  })).onChangeChannelType();

  var twValidationView = new TwValidationView({
    el: $adminForm,
    collection: new TwValidations()
  });

  var liOAuth2ValidationView = new LiOAuth2ValidationView({
    el: $adminForm,
    collection: new LiOAuth2Validations()
  });

  var eventsDispatcher = _.clone(Backbone.Events);

  var fbValidationView = new FbValidationView({
    el: $adminForm,
    collection: new FbValidations(),
    attributes: { dispatcher: eventsDispatcher }
  });

  var fbChannelView = new FbChannelView({
    el: $adminForm,
    collection: new FbChannels(),
    attributes: {
      dispatcher: eventsDispatcher,
      messagesview: fbValidationView
    }
  });

  var fbAlbumView = new FbAlbumView({
    el: $adminForm,
    collection: new FbAlbums(),
    attributes: {
      fbChannelView: fbChannelView
    }
  });

  var fbChValidationView = new FbChValidationView({
    el: $adminForm,
    collection: new FbChValidations()
  });

  var fbExtendView = new FbExtendView({
    el: $adminForm,
    collection: new FbExtends(),
    attributes: { dispatcher: eventsDispatcher }
  });

  var gplusValidationView = new GplusValidationView({
    el: $adminForm,
    collection: new GplusValidations()
  });

  var liGroupView = new LiGroupView({
    el: $adminForm,
    collection: new LiGroups()
  });

  var liOAuth2CompanyView = new LiOAuth2CompanyView({
    el: $adminForm,
    collection: new LiOAuth2Companies()
  });

  var vkValidationView = new VkValidationView({
    el: $adminForm,
    collection: new VkValidations()
  });

  var vkGroupView = new VkGroupView({
    el: $adminForm,
    collection: new VkGroups()
  });

  var scoopitValidationView = new ScoopitValidationView({
    el: $adminForm,
    collection: new ScoopitValidations()
  });

  var scoopitTopicView = new ScoopitTopicView({
    el: $adminForm,
    collection: new ScoopitTopics()
  });

  var tumblrValidationView = new TumblrValidationView({
    el: $adminForm,
    collection: new TumblrValidations()
  });

  var bloggerValidationView = new BloggerValidationView({
    el: $adminForm,
    collection: new BloggerValidations()
  });

  var xingValidationView = new XingValidationView({
    el: $adminForm,
    collection: new XingValidations()
  });

  var telegramValidationView = new TelegramValidationView({
    el: $adminForm,
    collection: new TelegramValidations()
  });

  var mediumValidationView = new MediumValidationView({
    el: $adminForm,
    collection: new MediumValidations()
  });

  var pushwooshValidationView = new PushwooshValidationView({
    el: $adminForm,
    collection: new PushwooshValidations()
  });

  var oneSignalValidationView = new OneSignalValidationView({
    el: $adminForm,
    collection: new OneSignalValidations()
  });

  var pushAlertValidationView = new PushAlertValidationView({
    el: $adminForm,
    collection: new PushAlertValidations()
  });

  var pagespeedValidationView = new PageSpeedValidationView({
    el: $adminForm,
    collection: new PageSpeedValidations()
  });

  var pinterestValidationView = new PinterestValidationView({
    el: $adminForm,
    collection: new PinterestValidations()
  });

  window.xtAppDispatcher = eventsDispatcher;

  try {
    if (
        !window.punycode &&
        typeof define == 'function' &&
        typeof define.amd == 'object' &&
        define.amd
    ) {
        require(['punycode'], function(punycode) {
            window.punycode = punycode;
        });
    }
  } catch (e) {

  }

});
