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

