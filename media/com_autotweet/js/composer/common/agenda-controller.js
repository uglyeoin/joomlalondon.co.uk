/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* globals angular,_ */

'use strict';

angular
  .module('starter.agenda-controller', ['starter.agenda-service'])
  .controller(
  'AgendaController',
  function ($scope, $rootScope, Agenda) {

    var agendaCtrl = $scope.agendaCtrl, agendas = agendaCtrl.agendas = Agenda
      .get();

    Agenda.clear();

    agendaCtrl.add = function () {
      var schedulingDate = agendaCtrl.scheduling_date_value;
      var schedulingTime = agendaCtrl.scheduling_time_value;

      if ((!schedulingDate) || (!schedulingTime)) {
        return;
      }

      schedulingDate = schedulingDate.trim();
      if (!schedulingDate.length) {
        return;
      }

      agendas.push({
        agendaDate: schedulingDate,
        agendaTime: schedulingTime
      });

      Agenda.put(agendas);

      agendaCtrl.scheduling_date_value = '';
    };

    agendaCtrl.remove = function (agenda) {
      agendas.splice(agendas.indexOf(agenda), 1);
      Agenda.put(agendas);
    };

    $rootScope.$on('newRequest', function () {
      Agenda.clear();
      agendas = agendaCtrl.agendas = Agenda.get();
    });

    $rootScope.$on('loadDate', function (event, schedulingDate) {
      agendaCtrl.scheduling_date_value = schedulingDate;
    });

    $rootScope.$on('loadTime', function (event, schedulingTime) {
      agendaCtrl.scheduling_time_value = schedulingTime;
    });

    $rootScope.$on('loadAgenda', function (event, param) {
      var output = [];

      _.each(param, function (item) {
        var schedulingDate, schedulingTime, parts = item
          .split(' ');

        schedulingDate = parts[0];
        schedulingTime = parts[1];

        // 14:45:00
        parts = schedulingTime.split(':');

        if (parts.length == 3) {
          parts.splice(2, 1);
          schedulingTime = parts.join(':');
        }

        output.push({
          agendaDate: schedulingDate,
          agendaTime: schedulingTime
        });
      });

      Agenda.put(output);
      agendas = agendaCtrl.agendas = Agenda.get();
      agendaCtrl.scheduling_date_value = '';
    });
  });
