/*!
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     http://https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

/* global angular, define, Backbone, alert, _ */

'use strict';

define('import', ['extlycore'], function (Core) {

  var ImportBegin = Core.ExtlyModel.extend({

    url: function () {
      return Core.SefHelper.route('index.php?option=com_autotweet&view=feeds&task=getImportBegin');
    }

  });

  var ImportStatus = Core.ExtlyModel.extend({

    url: function () {
      return Core.SefHelper.route('index.php?option=com_autotweet&view=feeds&task=getImportStatus');
    }

  });

  var ImportEnd = Core.ExtlyModel.extend({

    url: function () {
      return Core.SefHelper.route('index.php?option=com_autotweet&view=feeds&task=getImportEnd');
    }

  });

  var ImportView = Backbone.View.extend({

    events: {
      'click #toolbar-process button': 'onImportBegin',
      'click #toolbar-process .toolbar': 'onImportBegin',
      'click #cpanel-import': 'onImportBegin'
    },

    initialize: function () {
      var button;

      button = this.$('#toolbar-process button');
      button.attr('onclick', '');
      button.click(function (e) {
        e.preventDefault();
      });

      button = this.$('#toolbar-process .toolbar');
      button.attr('onclick', '');
      button.click(function (e) {
        e.preventDefault();
      });

      button = this.$('#cpanel-import');
      button.attr('onclick', '');
      button.click(function (e) {
        e.preventDefault();
      });

      this.resetBar(0);
    },

    onImportBegin: function () {
      var importStart = new ImportBegin(),
        token = this.$('#XTtoken').attr('name');

      this.$('.import-progress').removeClass('hide');
      this.$('.import-progress .success-message').addClass('hide');

      this.$('#toolbar-process button').addClass('disabled');
      this.$('#toolbar-process .toolbar').addClass('disabled');
      this.$('#cpanel-import').addClass('disabled');

      importStart.on('change', this.loadImportBegin, this);
      importStart.fetch({
        data: {
          _token: token
        },

        wait: true,
        dataType: 'text',
        error: function (model, fail, xhr) {
          alert(fail.responseText);
        }
      });
    },

    loadImportBegin: function (message) {
      if (message.get('status')) {
        this.feeds = message.get('feeds');

        this.nextFeed();
        this.onImportStatus(0);
      } else {
        alert(message.get('message'));
      }
    },

    onImportStatus: function (is_continue) {
      var importStatus = new ImportStatus(),
        token = this.$('#XTtoken').attr('name');

      importStatus.on('change', this.loadimportStatus, this);
      importStatus.fetch({
        data: {
          _token: token,
          feedId: this.currentFeed.id,
          isContinue: is_continue
        },

        wait: true,
        dataType: 'text',
        error: function (model, fail, xhr) {
          alert(fail.responseText);
        }
      });
    },

    loadimportStatus: function (message) {
      var completed;

      if (message.get('status')) {
        completed = message.get('completed');

        this.assignTotal(message.get('total'));
        if (completed) {
          if (_.size(this.feeds) === 0) {
            this.onImportEnd();
          } else {
            this.nextFeed();
            this.onImportStatus(0);
          }
        } else {
          this.onImportStatus(1);
        }
      } else {
        alert(message.get('message'));
      }
    },

    onImportEnd: function () {
      var importEnd = new ImportEnd(),
        token = this.$('#XTtoken').attr('name');

      importEnd.on('change', this.loadImportEnd, this);
      importEnd.fetch({
        data: {
          _token: token
        },

        wait: true,
        dataType: 'text',
        error: function (model, fail, xhr) {
          alert(fail.responseText);
        }
      });
    },

    loadImportEnd: function (message) {
      if (message.get('status')) {
        this.resetBar(100);

        this.$('.import-progress .success-message').removeClass('hide');

        this.$('#toolbar-process button').removeClass('disabled');
        this.$('#toolbar-process .toolbar').removeClass('disabled');
        this.$('#cpanel-import').removeClass('disabled');
      } else {
        alert(message.get('message'));
      }
    },

    assignFeedName: function (name) {
      var feedName = this.$('.import-progress .feed');
      feedName.val(name);
    },

    assignTotal: function (total) {
      var feedTotal = this.$('.import-progress .total');
      feedTotal.val(total);
    },

    resetBar: function (value) {
      var bar = this.$('.import-progress .bar');

      if (value) {
        this.barImport = value;
      } else {
        this.barImport = 1;
      }
      bar.attr('style', 'width: ' + this.barImport + '%;');
    },

    incBar: function () {
      var bar = this.$('.import-progress .bar');

      this.barImport += 5;
      if (this.barImport > 100) {
        this.resetBar(0);
      } else {
        bar.attr('style', 'width: ' + this.barImport + '%;');
      }
    },

    nextFeed: function () {
      var name;

      this.currentFeed = _.first(this.feeds);
      this.feeds = _.rest(this.feeds);

      name = this.currentFeed.id + ' - ' + this.currentFeed.name;
      this.assignFeedName(name);
      this.assignTotal(0);

      this.incBar();
    }

  }),

    importView = new ImportView({
      el: jQuery('body')
    });

});
