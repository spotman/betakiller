'use strict';

require([
  'jquery',
], function ($) {
  $(function () {

    class TestWampRpcManager {
      constructor() {
        this.resultAddBeginning = false;

        this.nodes           = new HtmlNodes('#testWampRpcManager');
        this.$results        = this.nodes.get('section.results');
        this.$resultTemplate = this.nodes.get('[data-template]', this.$results);

        this.nodes.getRoot()
          .on('click', '[data-action]', function (_this) {
            return function (event) {
              event.preventDefault();
              _this._action($(this));
            };
          }(this));
      }

      _action($trigger) {
        let action = $trigger.attr('data-action');
        switch (action) {
          case 'run':
            this._createResult($trigger).then();
            break;

          case 'removeResults':
            this._removeResults();
            break;

          default:
            throw new Error(`Unknown action "${action}"`);
        }
      }

      _removeResults() {
        this.nodes.get('section.results').find('iframe:not([data-template])').remove();
      }

      async _createResult($trigger) {
        let $control = this.nodes.get('section.control');
        let query    = {
          'connectionType': $trigger.attr('data-type'),
          'testsQty':       this.nodes.get('[name="testsQty"]', $control).val(),
          'qtyInPack':      this.nodes.get('[name="qtyInPack"]', $control).val(),
          'delayPack':      this.nodes.get('[name="delayPack"]', $control).val(),
        };

        let testUrl = this.nodes.getRoot().attr('data-testUrl');
        if (testUrl.indexOf('?') === -1) testUrl += '?';
        testUrl += $.param(query);

        let processesQty = this.nodes.get('[name="processesQty"]', $control).val();
        processesQty     = parseInt(processesQty);
        if (isNaN(processesQty)) processesQty = 0;

        let delayProcess = this.nodes.get('[name="delayProcess"]', $control).val();
        delayProcess     = parseInt(delayProcess);
        if (isNaN(delayProcess)) delayProcess = 0;

        for (let i = 0; i < processesQty; i++) {
          console.log('Run process:', (i + 1), '/', processesQty);

          if (i && delayProcess) {
            console.log('Process delay:', delayProcess);
            await this.sleep(delayProcess);
          }

          this.createTestFrame().attr('src', testUrl);
        }
      }

      createTestFrame() {
        let $frame = $(this.$resultTemplate[0].outerHTML);
        $frame
          .removeAttr('data-template')
          .show();

        if (!this.resultAddBeginning) {
          $frame.appendTo(this.$results);
        } else {
          $frame.prependTo(this.$results);
        }

        return $frame;
      }

      sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
      }
    }

    new TestWampRpcManager();

  });
});
