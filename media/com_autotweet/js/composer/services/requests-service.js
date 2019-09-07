/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* globals angular, Joomla, _ */

'use strict';

angular.module('starter.requests-service', ['extlycore', 'ngResource'])
  .factory('Requests', ['SefHelper', '$resource',
    function (SefHelper, $resource) {

      var url = SefHelper.route('index.php?option=com_autotweet&view=requests&format=json');
      var xTtoken = jQuery('#XTtoken').attr('name');

      var jsonParse = function (data) {
        var body = data.split(/@EXTLYSTART@|@EXTLYEND@/);

        if (body.length === 3) {
          return JSON.parse(body[1]);
        } else {
          return {
            status: false,
            message: data
          };
        }
      };

      return $resource(url, {}, {
        query: {
          method: 'POST',
          params: {
            task: '@taskCommand',
            _token: xTtoken
          },
          isArray: true,
          transformResponse: function (data, headersGetter) {
            return jsonParse(data);
          }
        },
        save: {
          method: 'POST',
          params: {
            task: '@taskCommand',
            _token: xTtoken,
            ref_id: '@ref_id'
          },
          transformResponse: function (data, headersGetter) {
            return jsonParse(data);
          }
        },
        get: {
          method: 'POST',
          params: {
            task: '@taskCommand',
            _token: xTtoken,
            id: '@request_id'
          },
          transformResponse: function (data, headersGetter) {
            return jsonParse(data);
          }
        }
      });
    }]);
