/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* globals angular, _, CryptoJS */

'use strict';

angular
  .module('starter.request-helper', [])
  .factory(
  'RequestHelper',
  [function () {
    var _this = this;

    _this.formatDatesTimes = function (agendas) {
      var dates = [];

      _.each(agendas, function (agenda) {
        dates.push(agenda.agendaDate + ' ' + agenda.agendaTime);
      });

      return dates;
    };

    _this.hasPastDates = function (agendas) {
      var today = new Date(), isPastDate = false;

      if ((!agendas) || (agendas.length === 0)) { return false; }

      try {
        _
          .each(
          agendas,
          function (dayTime) {
            var d, dateString = dayTime;

            if (dayTime
              .match(/^\d\d\d\d-\d\d-\d\d \d?\d:\d\d$/)) {
              dateString = dayTime
                .replace(' ', 'T')
                + ':00';
            }

            if (dayTime
              .match(/^\d\d\d\d-\d\d-\d\d \d?\d:\d\d:\d\d$/)) {
              dateString = dayTime
                .replace(' ', 'T');
            }

            d = new Date(dateString);

            if (d.getTime() < today.getTime()) {
              isPastDate = true;
            }
          });
      } catch (e) {
        isPastDate = true;
      }

      return isPastDate;
    };

    _this.getChannels = function (channels) {
      if ((_.isNumber(channels)) && (channels > 0)) {
        return [channels];
      }

      if ((_.isString(channels)) && (parseInt(channels) > 0)) {
        return [channels];
      }

      if (_.isArray(channels)) {
        return channels;
      }

      return [];
    };

    _this.getChannelsText = function (channels) {
      var options, channelsText;

      if (!window.channelchooser) { return ''; }

      options = angular.element(window.channelchooser).find(
        'option');

      options = _.filter(options, function (option) {
        return (option.selected) && (parseInt(option.value) > 0);
      });

      channelsText = _.reduce(options, function (memo, option) {
        var e = angular.element(option), txt = e.text();

        if (_.isEmpty(memo)) {
          return txt;
        } else {
          return memo + ', ' + txt;
        }
      }, '');

      return channelsText;
    };

    _this.isValidAgendaRepeat = function (agendas, unixMhdmd) {
      var invalid = ((unixMhdmd) && (unixMhdmd.length > 0) && (agendas.length > 1));

      return !invalid;
    };

    _this.generateRequestHash = function () {
      var now = new Date(), hash;

      hash = CryptoJS.MD5('' + now.getTime()
        + _.random(0, 9007199254740992));
      hash = CryptoJS.MD5(hash + _.random(0, 9007199254740992));
      hash = CryptoJS.MD5(hash + _.random(0, 9007199254740992));

      return hash.toString(CryptoJS.enc.Hex);
    };

    _this.isValidCronjobExpr = function (expr) {
      var testExpr = /^(((([0-9]+)(\,[0-9]+)*)|\*) ){4}((([0-9]+)(\,[0-9]+)*)|\*)$/;

      return ((_.isEmpty(expr)) || (testExpr.test(expr)));
    };

    return {
      formatDatesTimes: _this.formatDatesTimes,
      hasPastDates: _this.hasPastDates,
      getChannelsText: _this.getChannelsText,
      isValidAgendaRepeat: _this.isValidAgendaRepeat,
      generateRequestHash: _this.generateRequestHash,
      getChannels: _this.getChannels,
      isValidCronjobExpr: _this.isValidCronjobExpr
    };
  }]);
