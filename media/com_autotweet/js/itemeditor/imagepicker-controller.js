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

angular.module('starter.imagepicker-controller', ['starter.helper'])
  .controller('ImagePickerController', function ($scope, $rootScope, ItemEditorHelper) {

    var local = $scope.imagePickerCtlr,
      _this = this;

    _this.loadImages = function () {
      var rawimages = ItemEditorHelper.retrieveImages(), images = [];

      images.push({
        value: '',
        text: '-Select-',
        data_img_src: ''
      });

      _.each(rawimages, function (img) {
        var opt = {}, source;

        source = img.src;
        source = source.replace(parent.autotweetUrlBase, '');

        opt.value = source;
        opt.text = (_.isEmpty(img.alt) ? source : img.alt);

        if (source.indexOf('http') == -1) {
          source = parent.autotweetUrlRoot + source;
        };

        opt.data_img_src = source;
        images.push(opt);
      });

      local.images = images;
      $scope.$digest();

      // Delay to jQuery imagepicker
      setTimeout(function () {
        jQuery('#imagechooser').imagepicker({ show_label: true })
          .css('display', 'block')
          .css('margin-bottom', '9px');

        jQuery('.image_picker_selector li').first().remove();
      }, 1);
    };

    local.selectedImage = function () {
      $rootScope.$emit('selectedImage', local.imagechooser_value);
    };

    $rootScope.$on('loadImages', _this.loadImages);

  });
