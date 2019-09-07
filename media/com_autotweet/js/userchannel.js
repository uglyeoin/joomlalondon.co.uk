/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* global angular, _, define, Backbone, FB, define */

'use strict';

define('userchannel', ['extlycore', 'text!media/com_autotweet/js/userchannel/templates/enabled-channel.txt'],
		function (Core, channelTemplate) {

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

/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* global angular */

'use strict';

var AuthorizeAction = Core.ExtlyModel.extend({

  url: function () {
    return Core.SefHelper.route('index.php?option=com_autotweet&view=userchannels&task=authorizeAction');
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

/* global angular */

'use strict';

var Enabled = Core.ExtlyModel.extend({

  url: function () {
    return Core.SefHelper.route('index.php?option=com_autotweet&view=userchannels&task=addAuthChannel');
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

/* global angular */

'use strict';

var Pending = Core.ExtlyModel.extend({

  url: function () {
    return Core.SefHelper.route('index.php?option=com_autotweet&view=userchannels&task=getAuthParams');
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

/* global angular */

'use strict';

var PublishAction = Core.ExtlyModel.extend({

  url: function () {
    return Core.SefHelper.route('index.php?option=com_autotweet&view=userchannels&task=publishAction');
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

/* global angular */

'use strict';

var UnpublishAction = Core.ExtlyModel.extend({

  url: function () {
    return Core.SefHelper.route('index.php?option=com_autotweet&view=userchannels&task=unpublishAction');
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

/* global angular */

'use strict';

var Enableds = Backbone.Collection.extend({
		model: Enabled
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

/* global angular */

'use strict';

var Pendings = Backbone.Collection.extend({
		model: Pending
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

var EnabledView = Backbone.View.extend({

  initialize: function () {
    this.attributes.eventsHub.on('userChannel:authorized',
      this.addAuthChannel, this);
    this.$enabledList = this.$('#enabledList');
    this.collection.on('add', this.addOne, this);

    this.token = this.$('#XTtoken').attr('name');
    this.spinner = this.$(".loaderspinner");

    this.initializeSubviews();
  },

  addAuthChannel: function () {
    var view = this;

    view.spinner.addClass('loading');
    UserChannelHelper.showError(view, null);

    this.collection.create(this.collection.model, {
      attrs: {
        authParams: UserChannelHelper.getAuthParams(),
        token: view.token
      },

      wait: true,
      dataType: 'text',
      success: function (model, resp, options) {
        view.spinner.removeClass('loading');

        if (!model.get('status')) {
          UserChannelHelper.showError(view, model || 'Unknown error (EnabledView)');
        }
      },
      error: function (model, fail, xhr) {
        view.spinner.removeClass('loading');
        UserChannelHelper.showError(view, fail.responseText);
      }
    });

    return false;
  },
  addOne: function (channel) {
    var channelView;

    if (!channel.get('status')) {
      return false;
    }

    channelView = new ChannelView({
      model: channel,

      // Options
      attributes: {
        channelId: channel.get('id'),
        token: this.token,
        spinner: this.spinner,
        eventsHub: this.attributes.eventsHub
      }
    });

    this.$enabledList.removeClass('hide');
    this.$enabledList.append(channelView.render().el);
  },
  initializeSubviews: function (channel) {
    var view = this,
      channels = view.$('tr.enabled-channel'),
      channelId;

    _.each(channels, function (channel) {
      channelId = view.$(channel).find('.channel_id').val();

      new ChannelView({
        el: channel,
        model: (new Core.ExtlyModel()),

        // Options
        attributes: {
          channelId: channelId,
          token: view.token,
          spinner: view.spinner,
          eventsHub: view.attributes.eventsHub
        }
      });
    });
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

/* global angular, _, define, Backbone, UserChannelHelper, define */

'use strict';

var PendingView = Backbone.View.extend({

  events: {
    'click a.authorize-pending': 'onAuthorize'
  },
  initialize: function () {
    jQuery('#F0FHeaderHolder').hide();

    this.attributes.eventsHub.on('userChannel:re-authorize',
      this.onReAuthorize, this);
    this.token = this.$('#XTtoken').attr('name');
    this.spinner = this.$(".loaderspinner");
    this.$pendingChannel = null;
  },
  onAuthorize: function (e) {
    var view = this,
      $targetChannelType,
      channelTypeId;

    e.preventDefault();

    $targetChannelType = view.$(e.currentTarget);
    this.$pendingChannel = $targetChannelType.parents('.pending-channel');
    channelTypeId = this.$pendingChannel.find('.channeltype_id').val();

    this.spinner.addClass('loading');
    UserChannelHelper.showError(view, null);

    this.collection.create(this.collection.model, this.getAuthParamsCallback(view, {
      attrs: {
        channelTypeId: channelTypeId,
        token: view.token
      }
    }));

    return false;
  },
  getAuthParamsCallback: function (view, attrs) {
    var options = {
      wait: true,
      dataType: 'text',
      success: function (model, resp, options) {
        var status, params, callback;

        view.spinner.removeClass('loading');

        if (model.get('status')) {
          callback = model.get('callback');
          params = model.get('params');

          status = UserChannelHelper[callback](params, view);

          if (status === true) {
            view.authorizedChannel();
          } else if (status === false) {
            UserChannelHelper.showError(view, UserChannelHelper.getStatusMessage() || 'Unknown error (PendingView 2)');
          }
          // status = null => Authorizing
        } else {
          UserChannelHelper.showError(view, model || 'Unknown error (PendingView)');
        }
      },
      error: function (model, fail, xhr) {
        UserChannelHelper.showError(view, fail.responseText);
      }
    };

    return _.extend(options, attrs);
  },
  authorizedChannel: function () {

    // A new channel
    if (this.$pendingChannel) {
      this.$pendingChannel.remove();
      this.$pendingChannel = null;

      this.$('#no-auth-channels-msg').remove();
      this.attributes.eventsHub.trigger("userChannel:authorized");
    } else {
      // Re-authorizing an existing channel
      this.channelView.onReAuthorized();
      this.channelView = null;
    }
  },
  onReAuthorize: function (params) {
    var pending = new this.collection.model(),
      view = this;

    this.channelView = params.channelView;

    pending.save(null, this.getAuthParamsCallback(view, {
      attrs: {
        channelId: params.channelId,
        token: view.token
      }
    }));
  },
  showError: function (view, responseText) {
    if (responseText) {
      view.$('#alert-msg').removeClass('hide');
      view.$('#error-msg').html(responseText);
    } else {
      view.$('#alert-msg').addClass('hide');
    }
  }

});
    /* END - variables to be inserted here */

    var eventsHub = _.clone(Backbone.Events);

    var pendingView = new PendingView({
      el: jQuery('#adminForm'),
      collection: new Pendings(),
      attributes: { eventsHub: eventsHub }
    });

    var enabledView = new EnabledView({
      el: jQuery('#adminForm'),
      collection: new Enableds(),
      attributes: { eventsHub: eventsHub }
    });

  });

