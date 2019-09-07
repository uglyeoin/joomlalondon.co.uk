/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* global angular, _, alert */

'use strict';

angular.module('starter.helper', [])
  .factory('ItemEditorHelper', function () {

    var templateRender = null;

    var view = null;

    var ItemEditorHelper = {

      POSTTHIS_DEFAULT: 1,
      POSTTHIS_NO: 2,
      POSTTHIS_YES: 3,

      setHtmlAdvancedAttrs: function (message) {
        var $form, element;

        element = view.document.getElementById('autotweet_advanced_attrs');

        if (element) {
          message = JSON.stringify(message);
          element.value = message;

          return true;
        }

        message = _.escape(JSON.stringify(message));

        element = '<input type="hidden" id="autotweet_advanced_attrs" name="autotweet_advanced_attrs" value="'
          + message
          + '">';

        $form = view.jQuery('form[name=adminForm]');

        // Form not found
        if (!$form.length) {
          // Zoo form?
          $form = view.jQuery('form[name=submissionForm]');

          // Form not found
          if (!$form.length) {
            // EasyBlog 5 form?
            $form = view.jQuery('form[name=composer]');
          }
        }

        // Form not found
        if ($form.length == 1) {
          $form.append(element);
        } else {
          alert('Joocial - Form not found or more than one.');
        }

        return true;
      },

      getHtmlAdvancedAttrs: function () {
        var input, message;

        input = view.document.getElementById('autotweet_advanced_attrs');
        if ((input) && (!_.isEmpty(input.value))) {
          message = input.value;
          message = JSON.parse(_.unescape(message));

          return message;
        }

        return null;
      },

      getHtmlEditorText: function () {
        var editor, text;

        if ((view.tinymce) && (view.tinymce.activeEditor)) {
          text = view.tinymce.activeEditor.getContent();

          // No text, but in Zoo there's a change to load it via editors
          if (_.isEmpty(text)) {
            text = view.tinymce.editors[0].getContent();
          }

          return text;
        } else if (editor = view.document.getElementById('jform_articletext')) {

          return editor.value;
        }

        return null;
      },

      setHtmlPanel: function (panel, title) {
        var element, tabs;

        // com_content - J3 - admin
        element = view.jQuery('#item-form #myTabContent');
        if (element.length) {
          ItemEditorHelper.addHtmlTab(title);
          ItemEditorHelper.addHtmlPanel(element, panel);
          return true;
        }

        // com_content - J3 - front
        element = view.jQuery('.item-page #adminForm .tab-content');
        if (element.length) {
          ItemEditorHelper.addHtmlTab(title);
          ItemEditorHelper.addHtmlPanel(element, panel);
          return true;
        }

        // com_autotweet requests qTypeTabs
        element = view.jQuery('.request-edit #qContent');
        if (element.length) {
          ItemEditorHelper.addHtmlTab(title);
          ItemEditorHelper.addHtmlPanel(element, panel);
          tabs = view.jQuery('#filterconditions-tab a');

          if (tabs.length) {
            tabs.tab('show');
          }

          return true;
        }

        // com_k2
        element = view.jQuery('#adminFormK2Sidebar');
        if (element.length) {
          ItemEditorHelper.addHtmlPanel(element, panel);
          return true;
        }

        // com_zoo - admin
        element = view.jQuery('#parameter-accordion');
        if (element.length) {
          ItemEditorHelper.addHtmlPanel(element, panel);
          return true;
        }

        // com_zoo - front
        element = view.jQuery('#item-submission');
        if (element.length) {
          ItemEditorHelper.addHtmlPanel(element, panel);
          return true;
        }

        // com_easyblog 5
        element = view.jQuery('form[name=composer]');
        if (element.length) {
          ItemEditorHelper.addHtmlPanel(element, panel);
          return true;
        }

        // com_easyblog - admin - J3
        element = view.jQuery('#eblog-wrapper #options');
        if (element.length) {
          ItemEditorHelper.addHtmlPanel(element, panel);
          return true;
        }

        // com_easyblog - front
        element = view.jQuery('#widget-writepost .ui-modbody');
        if (element.length) {
          ItemEditorHelper.addHtmlPanel(element, panel);
          return true;
        }

        // com_flexicontent - J3 - admin
        element = view.jQuery('#fcform_tabset_0');
        if (element.length) {
          ItemEditorHelper.addHtmlPanel(element, panel);
          element.height('100%');
          return true;
        }

        // com_jreviews
        element = view.jQuery('.jr-tabs');
        if (element.length) {
          ItemEditorHelper.addHtmlPanel(element, panel);
          return true;
        }

        // com_jcalpro
        element = view.jQuery('#JCalProEventTabContent');
        if (element.length) {
          ItemEditorHelper.addHtmlTab(title);
          ItemEditorHelper.addHtmlPanel(element, panel);
          return true;
        }

        // General Joomla 3 - Component
        element = view.jQuery('#content');
        if (element.length) {
          ItemEditorHelper.addHtmlPanel(element, panel);
          return true;
        }

        // General Joomla 2.5 - Component
        element = view.jQuery('#element-box div.m');
        if (element.length) {
          ItemEditorHelper.addHtmlPanel(element, panel);
          return true;
        }

        return false;
      },

      addHtmlPanel: function (sidebar, panel) {
        var elem;

        elem = view.document.getElementById('autotweet-advanced-attrs');

        if (elem) {
          elem.parentNode.removeChild(elem);
        }

        sidebar.append(panel);
      },

      loadPanel: function (advancedAttrs) {
        if (_.isEmpty(advancedAttrs)) {
          advancedAttrs = view.autotweetAdvancedAttrs;
        }

        if (_.isEmpty(advancedAttrs)) {
          return false;
        }

        if (templateRender) {
          ItemEditorHelper.updatePanel(advancedAttrs);
        } else {
          jQuery.ajax({
            url: view.autotweetPanelTemplate,
            dataType: "text",
            success: function (template) {
              templateRender = _.template(template),
                ItemEditorHelper.updatePanel(advancedAttrs);
            }
          });
        }
      },

      updatePanel: function (advancedAttrs) {
        var panel, tplAttrs;

        tplAttrs = _.clone(advancedAttrs);
        tplAttrs.autotweetUrlRoot = view.autotweetUrlRoot;

        panel = templateRender(tplAttrs);
        ItemEditorHelper.setHtmlPanel(panel, advancedAttrs.editorTitle);

        // First tab on the caller
        parent.jQuery('ul.nav-tabs li a:first').tab('show');
      },

      addHtmlTab: function (title) {
        var tabs;

        // Joomla 3 - Admin
        tabs = view.jQuery('#myTabTabs');
        if (tabs.length) {
          ItemEditorHelper._addHtmlTab(title, tabs);
          return true;
        }

        // Joomla 3 - Front
        tabs = view.jQuery('#adminForm ul.nav-tabs');
        if (tabs.length) {
          ItemEditorHelper._addHtmlTab(title, tabs);
          return true;
        }

        // JCalPro
        tabs = view.jQuery('#JCalProEventTabTabs');
        if (tabs.length) {
          ItemEditorHelper._addHtmlTab(title, tabs);
          return true;
        }

        // com_autotweet requests qTypeTabs
        tabs = view.jQuery('.request-edit #qTypeTabs ul.nav-tabs');
        if (tabs.length) {
          ItemEditorHelper._addHtmlTab(title, tabs);
          return true;
        }

        return false;
      },

      _addHtmlTab: function (title, tabs) {
        var img = '<img src="' + view.autotweetUrlRoot + 'media/com_autotweet/images/autotweet-icon.png">',
          tab;

        if (!view.jQuery('#myAutoTweetTab').length) {
          tab = view.jQuery('<li id="myAutoTweetTab" class=""><a id="myAutoTweetTabToogle" href="#autotweet-advanced-attrs" data-toggle="tab">' + img + ' ' + title + '</a></li>');
          tabs.append(tab);

          view.jQuery('#myAutoTweetTabToogle').click(function (e) {
            e.preventDefault();

            if (_.isFunction(jQuery(this).tab)) {
              jQuery(this).tab('show');
            }
          });
        }
      },

      retrieveTitle: function () {
        var title;

        // Joomla
        title = view.jQuery('#jform_title').val();

        if (!_.isEmpty(title)) {
          return title;
        }

        // K2
        title = view.jQuery('#title').val();

        if (!_.isEmpty(title)) {
          return title;
        }

        // EasyBlog 5
        title = view.jQuery('textarea[name=title]').val();

        if (!_.isEmpty(title)) {
          return title;
        }

        // Zoo
        title = view.jQuery('#name').val();

        if (!_.isEmpty(title)) {
          return title;
        }

        return '';
      },

      retrieveFulltext: function () {
        var text = ItemEditorHelper.getHtmlEditorText();
        var tmp = document.createElement("DIV");

        tmp.innerHTML = text;
        text = tmp.textContent || tmp.innerText || "";

        if (text.length > 300) {
          text = text.substr(0, 300) + '...';
        }

        return text;
      },

      retrieveImages: function () {
        var text, $dummyNode, imgs, img, $i, imgsrc, imgalt;

        text = ItemEditorHelper.getHtmlEditorText();
        text = text.replace(/src/g, 'data_src');

        $dummyNode = jQuery(text),

          imgs = [];

        _.each($dummyNode.find('img'), function (i) {
          $i = jQuery(i);
          img = {
            src: $i.attr('data_src'),
            alt: $i.attr('alt')
          };
          imgs.push(img);
        });

        // Joomla
        imgsrc = view.jQuery('#jform_images_image_intro').val();
        if (!_.isEmpty(imgsrc)) {
          imgalt = view.jQuery('#jform_images_image_intro_alt').val() || view.jQuery('#jform_images_image_intro_caption').val();
          img = {
            src: imgsrc,
            alt: imgalt
          };
          imgs.push(img);
        }

        imgsrc = view.jQuery('#jform_images_image_fulltext').val();
        if (!_.isEmpty(imgsrc)) {
          imgalt = view.jQuery('#jform_images_image_fulltext_alt').val() || view.jQuery('#jform_images_image_fulltext_caption').val();
          img = {
            src: imgsrc,
            alt: imgalt
          };
          imgs.push(img);
        }

        // K2
        imgsrc = view.jQuery('.k2AdminImage').attr('src');
        if (!_.isEmpty(imgsrc)) {
          imgsrc = imgsrc.slice(0, imgsrc.indexOf('_S.jpg'));

          imgalt = view.jQuery('input[name=image_caption]').val();

          // XS
          img = {
            src: imgsrc + '_XS.jpg',
            alt: imgalt + ' XS'
          };
          imgs.push(img);

          // S
          img = {
            src: imgsrc + '_S.jpg',
            alt: imgalt + ' S'
          };
          imgs.push(img);

          // Generic
          img = {
            src: imgsrc + '_Generic.jpg',
            alt: imgalt + ' Generic'
          };
          imgs.push(img);

          // M
          img = {
            src: imgsrc + '_M.jpg',
            alt: imgalt + ' M'
          };
          imgs.push(img);

          // L
          img = {
            src: imgsrc + '_L.jpg',
            alt: imgalt
          };
          imgs.push(img);

          // XL
          img = {
            src: imgsrc + '_XL.jpg',
            alt: imgalt + ' XL'
          };
          imgs.push(img);
        }

        // Zoo
        imgsrc = view.jQuery('.image-preview img');
        if (imgsrc.length) {
          _.each(imgsrc, function (source) {
            img = {
              src: source.src
            };

            if (source.src.toLowerCase().match(/\.(jpg|jpeg|png|gif)$/)) {
              imgs.push(img);
            }
          });
        }

        // EasyBlog
        imgsrc = view.jQuery('.imageData').val();
        if (!_.isEmpty(imgsrc)) {
          imgsrc = JSON.parse(_.unescape(imgsrc));
          img = {
            src: imgsrc.url,
            alt: imgsrc.title
          };
          imgs.push(img);
        }

        // E-Shop
        imgsrc = view.jQuery('input[name=product_image]+img').attr('src');
        if (!_.isEmpty(imgsrc)) {
          imgs.push({
            src: imgsrc
          });
        }

        imgsrc = view.jQuery('#product_images_area img');
        if (imgsrc.length) {
          _.each(imgsrc, function (source) {
            img = {
              src: source.src
            };
            imgs.push(img);
          });
        }

        return imgs;
      },

      setYesNo: function (v, $field) {
        var dataValue;

        if ((v) && (v != $field.val())) {
          dataValue = $field.parent().find('a[data-value=' + v + ']');

          // If there's a button
          if (dataValue.length) {
            dataValue.click();
          } else {
            $field.val(v);
            $field.trigger('liszt:updated');
          }
        }
      }
    };

    // Loading values from the plugin itself
    if (window.parent.autotweetAdvancedAttrs) {
      // Regular case
      view = window.parent;
    } else {
      // EasyBlog 5
      if (window.autotweetAdvancedAttrs) {
        view = window;
      }
    }

    ItemEditorHelper.loadPanel();

    if (view) {
      ItemEditorHelper.setHtmlAdvancedAttrs(view.autotweetAdvancedAttrs);
    }

    return ItemEditorHelper;
  });
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

angular.module('starter.jquery-extras', [])
  .run(function () {
    angular.element(document).ready(function () {

      // Ease on ChosenJS
      jQuery('#itemEditorTabsContent .xt-tab-pane').addClass("tab-pane fade");

      // Tabs
      var tabs = jQuery('#itemEditorTabs a:first');

      if (tabs.length) {
        tabs.tab('show');
      }

      // First tab on the caller
      tabs = window.parent.jQuery('ul.nav-tabs li a:first');

      if (tabs.length) {
        tabs.tab('show');
      }

    });
  });

/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

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

/**
 * Services that persists and retrieves Agendas from localStorage
 */
angular.module('starter.agenda-service', [])
  .factory('Agenda', function () {

    var STORAGE_ID = 'agendas-autotweet',
      _this = this;

    _this.get = function () {
      return JSON.parse(localStorage.getItem(STORAGE_ID) || '[]');
    };

    _this.put = function (todos) {
      localStorage.setItem(STORAGE_ID, JSON.stringify(todos));
    };

    _this.clear = function () {
      this.put([]);
    };

    return {
      get: _this.get,
      put: _this.put,
      clear: _this.clear
    };

  });
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

angular.module('starter.message-controller', []).controller(
  'MessageController',
  function ($scope, $rootScope) {

    var _this = this || {};
    var local = $scope.messageCtrl;

    local.remainingCount = 0;
    local.remainingCountClass = '';

    local.countRemaining = function () {
      var style, c, h;

      c = (local.description_value ? local.description_value.length : 0);
      h = (_.isEmpty(_this.hashtags_value) ? 0 : _this.hashtags_value.length + 1);

      c = c + h;
      style = 'label';

      if ((c > 0) && (c <= 60)) {
        style = 'label label-success';
      } else if ((c > 60) && (c <= 100)) {
        style = 'label label-warning';
      } else if (c > 100) {
        style = 'label label-important';
      }

      local.remainingCount = c;
      local.remainingCountClass = style;
    };

    _this.updateMessageView = function (e, desc, hash) {
      local.description_value = desc;

      if (window.hashtags) {
        local.hashtags_value = hash;
      }

      local.countRemaining();
    };

    $rootScope.$on('updateMessageView', _this.updateMessageView);

  });
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
/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* global angular,_,unix_mhdmd,description,hashtags,itemeditor_postthis,itemeditor_evergreen,image_url */

'use strict';

angular.module('starter.itemeditor-controller', [
  'starter.helper',
  'starter.agenda-service',
  'starter.request-helper',
  'ui.bootstrap.buttons',
  'localytics.directives'])
  .controller('ItemEditorController', function ($scope, $rootScope, ItemEditorHelper, RequestHelper, Agenda) {

    var local = $scope.itemEditorCtrl,
      _this = this;

    local.showDialog = false;
    local.messageText = '';

    _this.assignImage = function (e, imageUrl) {
      if (!_.isEmpty(imageUrl)) {
        local.image_url_value = imageUrl;
      }
    };

    _this.itemeditorInit = function () {
      var attrs;

      attrs = ItemEditorHelper.getHtmlAdvancedAttrs();
      if (attrs) {
        $rootScope.$emit('loadMessage', attrs);
      }
    };

    _this.loadMessage = function (e, message) {
      if (_.isEmpty(message.description)) {
        message.description = ItemEditorHelper.retrieveTitle();
      }

      if (_.isEmpty(message.fulltext)) {
        message.fulltext = ItemEditorHelper.retrieveFulltext();
      }

      // Shared with MessageController
      $rootScope.$emit('updateMessageView', message.description, message.hashtags);

      local.fulltext_value = message.fulltext;

      $rootScope.$emit('loadImages');
      local.image_url_value = message.image_url;

      // Shared with AgendaController
      $rootScope.$emit('loadAgenda', message.agenda);

      // Radio Buttons - Generate click to set values - delay to avoid inprog error
      setTimeout(function () {
        angular.element(document.getElementById('ctrl_itemeditor_postthis_'
          + message.postthis)).click();
        angular.element(document.getElementById('ctrl_itemeditor_evergreen_'
          + message.evergreen)).click();
      }, 1);

      local.channelchooser_value = message.channels;

      // Shared with CronjobExprController field
      angular.element(unix_mhdmd).val(message.unix_mhdmd);

      local.repeat_until_value = message.repeat_until;
    };

    local.onSubmit = function () {
      var descr, message, agendas, unixMhdmdValue;

      // Read it from Message Controller field - description_value
      descr = angular.element(description).val();
      descr = descr.trim();

      message = parent.autotweetAdvancedAttrs || {};
      message.description = descr;

      // Read it from Message Controller field - hashtags_value
      message.hashtags = angular.element(hashtags).val();

      message.fulltext = local.fulltext_value;

      // Radio Buttons
      message.postthis = angular.element(itemeditor_postthis).val();
      message.evergreen = angular.element(itemeditor_evergreen).val();

      agendas = Agenda.get();
      agendas = RequestHelper.formatDatesTimes(agendas);

      // Check Agenda
      if (RequestHelper.hasPastDates(agendas)) {
        _this.error({
          statusText: 'Agenda has dates in the past. Please, remove them.'
        });

        return false;
      }

      // From CronjobExprController field - unixMhdmdValue
      unixMhdmdValue = angular.element(unix_mhdmd).val();

      // Valid Expression
      if (!RequestHelper.isValidCronjobExpr(unixMhdmdValue)) {
        _this.error({
          statusText: 'Repeat Expression is invalid.'
        });

        return false;
      }

      // Check Agenda
      if (!RequestHelper.isValidAgendaRepeat(agendas, unixMhdmdValue)) {
        _this.error({
          statusText: 'Repeat Expression not allowed for more than one Agenda date.'
        });

        return false;
      }

      message.agenda = agendas;
      message.unix_mhdmd = unixMhdmdValue;
      message.repeat_until = local.repeat_until_value;

      // Just in jQuery Selection
      if (_.isEmpty(local.image_url_value)) {
        local.image_url_value = angular.element(image_url).val();
      }

      message.image_url = local.image_url_value;
      message.channels = local.channelchooser_value;
      message.channels_text = RequestHelper.getChannelsText(local.channelchooser_value);

      ItemEditorHelper.loadPanel(message);
      ItemEditorHelper.setHtmlAdvancedAttrs(message);

      // window.parent.xtModalClose();
      var btn = window.parent.jQuery('.mce-close');

      if (!btn.length) {
        btn = window.parent.jQuery('#joocial_menu_modal .modal-header button');
      }

      btn.click();

      if (window.parent.jModalClose) {
        window.parent.jModalClose();
      }
    };

    _this.error = function (msg) {
      local.showDialog = true;
      local.messageText = msg.statusText;
    };

    $rootScope.$on('loadMessage', _this.loadMessage);
    $rootScope.$on('selectedImage', _this.assignImage);
    $rootScope.$on('itemeditorInit', _this.itemeditorInit);
  })
  .run(function ($rootScope) {
    angular.element(document).ready(function () {
      $rootScope.$emit('itemeditorInit');
    });
  });
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
