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

"use strict";

(function () {
  var deps = ['starter.message-controller',
    'starter.editor-controller',
    'starter.requests-controller',
    'starter.agenda-controller',
    'starter.jquery-extras'];

  // Joocial
  try {
    angular.module('starter.cronjob-expr-controller');
    deps.push('starter.cronjob-expr-controller');
  } catch (e) {
  }

  angular.module('starter', deps)
    .config(function ($logProvider, $compileProvider) {

      // Debug Application
      $logProvider.debugEnabled(false);
      $compileProvider.debugInfoEnabled(false);

    });
})();
