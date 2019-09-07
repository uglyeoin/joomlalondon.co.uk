/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* global define, Backbone */

'use strict';

define('manager', ['extlycore'], function () {

  jQuery('#managerTabs a:first').tab('show');

  var ManagerView = Backbone.View.extend({

    events: {
      'click .works7x24 a': 'onChangeWorks7x24'
    },

    initialize: function () {
      this.onChangeWorks7x24();
    },

    onChangeWorks7x24: function (event) {
      var value = this.$('#xtformsave_works7x24').val();
      if (
        // Not an event, real value
        ((!event) && (value === "1"))

        // Event, not processed yet, so inverse value
        || ((event) && (value === "0"))
        ) {
        this.$('.group-works7x24').fadeOut();
      } else {
        this.$('.group-works7x24').fadeIn();
      }
    }

  });

  var managerView = new ManagerView({
    el: jQuery('#adminForm')
  });

});
