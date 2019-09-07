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

  function StatsView(attrs) {
    this.view = this;
    var view = this.view;

    view.attrs = attrs;

    view.$ = function (selector) {
      return jQuery(view.attrs.el).find(selector);
    };

    view.loadStats();
  }

  StatsView.prototype.loadStats = function () {
    if ((window.nv) && (window.requestsData)) {
      nv.addGraph(function () {
        var chart = nv.models.pieChart()
          .x(function (d) { return d.label; })
          .y(function (d) { return d.value; })
          .showLabels(true)
          .labelType("value");

        d3.select("#requests-chart svg")
          .datum(requestsData)
          .call(chart);

        return chart;
      });
    }

    if ((window.nv) && (window.postsData)) {
      nv.addGraph(function () {
        var chart = nv.models.pieChart()
          .x(function (d) { return d.label; })
          .y(function (d) { return d.value; })
          .showLabels(true)
          .labelType("percent");

        d3.select("#posts-chart svg")
          .datum(postsData)
          .call(chart);

        return chart;
      });
    }

    if ((window.nv) && (window.timelineData)) {
      nv.addGraph(function () {
        var chart = nv.models.lineChart()
          .margin({ left: 100 })
          .useInteractiveGuideline(true)
          .showLegend(true)
          .showYAxis(true)
          .showXAxis(true);

        chart.xAxis
          .axisLabel('Date')
          .tickFormat(function (d) {
            return d3.time.format('%Y-%m-%d')(new Date(d * 1000));
          });

        chart.yAxis
          .axisLabel('Messages')
          .tickFormat(d3.format('.f'));

        d3.select('#posts-timeline svg')
          .datum(timelineData)
          .call(chart);

        nv.utils.windowResize(function () { chart.update(); });

        return chart;
      });
    }
  };

  jQuery(document).ready(function () {
    var el = $('#adminForm');
    var liveUpdateView;

    liveUpdateView = new StatsView({
      el: el,
      collection: {}
    });

  });

})(window.jQuery, window, document);
