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

