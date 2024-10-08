'use strict';

import BetakillerWampFacade from '@betakiller/wamp-wrapper';
import HtmlNodes from './HtmlNodes';

class TestWampRpcManager {
  constructor() {
    this.resultAddBeginning = false;

    this.nodes = new HtmlNodes('#testWampRpcManager');
    this.$results = this.nodes.get('section.results');
    this.$resultTemplate = this.nodes.get('[data-template]', this.$results);

    const rootNode = this.nodes.getRoot();

    rootNode
      .on('click', '[data-action]', function (_this) {
        return function (event) {
          event.preventDefault();
          _this._action($(this));
        };
      }(this));

    this.testCases = rootNode.attr('data-cases').split(',');
    this.nodes.get('.userAgentOriginal').html(window.navigator.userAgent);

    let WampFacade = new BetakillerWampFacade();
    this.nodes.get('.userAgentWamp').html(WampFacade.options.auth_secret);
  }

  _action($trigger) {
    let action = $trigger.attr('data-action');
    switch (action) {
      case 'run':
        this._createResult($trigger.attr('data-type')).then();
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

  async _createResult(connectionType) {
    const _this = this;
    this.testCases.forEach(function(testCase) {
      _this._createResultTestCase(connectionType, testCase).then();
    });
  }

  async _createResultTestCase(connectionType, testCase) {
    let $control = this.nodes.get('section.control');
    let query = {
      'case': testCase,
      'connectionType': connectionType,
      'testsQty': this.nodes.get('[name="testsQty"]', $control).val(),
      'qtyInPack': this.nodes.get('[name="qtyInPack"]', $control).val(),
      'delayPack': this.nodes.get('[name="delayPack"]', $control).val(),
    };

    let testUrl = this.nodes.getRoot().attr('data-testUrl');
    if (testUrl.indexOf('?') === -1) testUrl += '?';
    testUrl += $.param(query);

    let processesQty = this.nodes.get('[name="processesQty"]', $control).val();
    processesQty = parseInt(processesQty);
    if (isNaN(processesQty)) processesQty = 0;

    let delayProcess = this.nodes.get('[name="delayProcess"]', $control).val();
    delayProcess = parseInt(delayProcess);
    if (isNaN(delayProcess)) delayProcess = 0;

    for (let i = 0; i < processesQty; i++) {
      console.log('Test. Run process:', (i + 1), '/', processesQty);

      if (i && delayProcess) {
        console.log('Test. Process delay:', delayProcess);
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
