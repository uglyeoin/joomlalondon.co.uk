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
