/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* global jQuery, Request, Joomla, alert, Backbone */

'use strict';

var FeedView = Backbone.View.extend({

  events: {
    'change #xtformcontenttype_id': 'onChangeContentType',
    'change #xtformsave_author': 'onChangeSaveAuthor',
    'click .source-fulltext a': 'onChangeSourceFullText',
    'click .process_enclosures a': 'onChangeProcEnclosures',
    'click .trackback a': 'onChangeTrackback',
    'click .save_img a': 'onChangeSaveImg',
    'click .text_filter a': 'onChangeTextFilter',
    'click .filtering a': 'onChangeFiltering',
    'click .duplicates a': 'onChangeDuplicates',
    'click .combine a': 'onChangeCombine'
  },

  initialize: function () {
    // Activate Tabs
    this.$('#feed_data a[data-toggle=tab]').tab();
    this.$('#feed_data a[data-toggle=tab]').click(function (e) {
      e.preventDefault();
    });
    this.$('#feed_data a:first').tab('show');

    this.onChangeContentType();
    this.onChangeSaveAuthor();
    this.onChangeSourceFullText();
    this.onChangeProcEnclosures();
    this.onChangeTrackback();
    this.onChangeSaveImg();
    this.onChangeTextFilter();
    this.onChangeFiltering();
    this.onChangeDuplicates();
    this.onChangeCombine();
  },

  onChangeContentType: function () {
    var contentType = this.$('#xtformcontenttype_id').val(),
      cats, selected,
      catlist = this.$('#catid');

    cats = window.feedCategories[contentType];

    selected = catlist.val();
    catlist.empty();

    _.each(cats, function (c) {
      var opt = new Option();
      opt.value = c.id;
      opt.text = c.title + ' (' + c.id + ')';
      catlist.append(opt);
    });

    catlist.val(selected);
    catlist.trigger('liszt:updated');
  },

  onChangeSaveAuthor: function () {
    var save_author = this.$('#xtformsave_author').val();
    if ((save_author === "2") || (save_author === "4")) {
      this.$('.group-author-alias').fadeIn();
    } else {
      this.$('.group-author-alias').fadeOut();
    }
  },

  onChangeSourceFullText: function (event) {
    var value = this.$('#xtformsave_sourcefulltext').val();
    if (
      // Not an event, real value
      ((!event) && (value === "1"))

      // Event, not processed yet, so inverse value
      || ((event) && (value === "0"))
    ) {
      this.$('.group-source-fulltext').fadeIn();
    } else {
      this.$('.group-source-fulltext').fadeOut();
    }
  },

  onChangeProcEnclosures: function (event) {
    var value = this.$('#xtformsave_processenc').val();
    if (
      // Not an event, real value
      ((!event) && (value === "1"))

      // Event, not processed yet, so inverse value
      || ((event) && (value === "0"))
    ) {
      this.$('.group-processenc').fadeIn();
    } else {
      this.$('.group-processenc').fadeOut();
    }
  },

  onChangeTrackback: function (event) {
    var value = this.$('#xtformsave_trackback').val();
    if (
      // Not an event, real value
      ((!event) && (value === "1"))

      // Event, not processed yet, so inverse value
      || ((event) && (value === "0"))
    ) {
      this.$('.group-trackback').fadeIn();
    } else {
      this.$('.group-trackback').fadeOut();
    }
  },

  onChangeSaveImg: function (event) {
    var value = this.$('#xtformsave_save_img').val();
    if (
      // Not an event, real value
      ((!event) && (value === "1"))

      // Event, not processed yet, so inverse value
      || ((event) && (value === "0"))
    ) {
      this.$('.group-save_img').fadeIn();
    } else {
      this.$('.group-save_img').fadeOut();
    }
  },

  onChangeTextFilter: function (event) {
    var value = this.$('#xtformsave_text_filter').val();
    if (
      // Not an event, real value
      ((!event) && (value === "1"))

      // Event, not processed yet, so inverse value
      || ((event) && (value === "0"))
    ) {
      this.$('.group-text_filter').fadeIn();
    } else {
      this.$('.group-text_filter').fadeOut();
    }
  },

  onChangeFiltering: function (event) {
    var value = this.$('#xtformsave_filtering').val();
    if (
      // Not an event, real value
      ((!event) && (value === "1"))

      // Event, not processed yet, so inverse value
      || ((event) && (value === "0"))
    ) {
      this.$('.group-filtering').fadeIn();
    } else {
      this.$('.group-filtering').fadeOut();
    }
  },

  onChangeDuplicates: function (event) {
    var value = this.$('#xtformsave_duplicates').val();
    if (
      // Not an event, real value
      ((!event) && (value === "1"))

      // Event, not processed yet, so inverse value
      || ((event) && (value === "0"))
    ) {
      this.$('.group-duplicates').fadeIn();
    } else {
      this.$('.group-duplicates').fadeOut();
    }
  },

  onChangeCombine: function (event) {
    var value = this.$('#xtformsave_combine').val();
    if (
      // Not an event, real value
      ((!event) && (value === "0"))

      // Event, not processed yet, so inverse value
      || ((event) && (value === "1"))
    ) {
      this.$('.group-combine').fadeIn();
    } else {
      this.$('.group-combine').fadeOut();
    }
  }

});
