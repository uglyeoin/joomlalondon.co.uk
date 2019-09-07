/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* global jQuery, Request, Joomla, alert, Core, nv, d3, requestsData, postsData, timelineData */

'use strict';

(function ($, window, document, undefined) {

  function LiveUpdateView(attrs) {
    this.view = this;
    var view = this.view;

    view.attrs = attrs;

    view.$ = function (selector) {
      return jQuery(view.attrs.el).find(selector);
    };

    view.$updateNotice = view.$("#updateNotice");
    view.$spinner = view.$(".loaderspinner72");
    view.url = Core.SefHelper.route('index.php?option=com_autotweet&view=cpanels&task=getUpdateInfo');

    $(document).ajaxStart(function () {
      view.$spinner.addClass('loading72');
    });

    $(document).ajaxStop(function () {
      view.$spinner.removeClass('loading72');
    });

    view.getLiveUpdates();
  }

  LiveUpdateView.prototype.getLiveUpdates = function () {
    var view = this;
    var postData = {
      'token': view.$('#XTtoken').attr('name')
    };

    $.ajax({
      url: view.url,
      type: "POST",
      contentType: 'application/json',
      data: JSON.stringify(postData),
      success: jQuery.proxy(view.loadLiveUpdates, view),
      error: function (jqXHR, textStatus, errorThrown) {
        alert(textStatus);
      }
    });
  };

  LiveUpdateView.prototype.loadLiveUpdates = function (response) {
    var resp = Core.ExtlyModel.parse(response);

    if (resp.hasUpdate) {
      this.$updateNotice.html(resp.result);
    }
  };

  jQuery(document).ready(function () {
    var el = $('#adminForm');
    var liveUpdateView;

    liveUpdateView = new LiveUpdateView({
      el: el,
      collection: {}
    });

  });

})(window.jQuery, window, document);
