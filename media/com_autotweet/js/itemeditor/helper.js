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
