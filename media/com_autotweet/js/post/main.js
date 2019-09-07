/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* global angular, _, define, PostView */

'use strict';

define('post', [], function () {

  /* BEGIN - variables to be inserted here */

  /* END - variables to be inserted here */

  var postView = new PostView({
    el: jQuery('#adminForm')
  });

  return postView;

});
