/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* global angular, _ */

'use strict';

angular.module('starter', [
  'starter.agenda-controller',
  'starter.message-controller',
  'starter.cronjob-expr-controller',
  'starter.imagepicker-controller',
  'starter.jquery-extras',
  'starter.itemeditor-controller'
])

  .config(function ($logProvider, $compileProvider) {

    // Debug Application
    $logProvider.debugEnabled(false);
    $compileProvider.debugInfoEnabled(false);

  });
