'use strict';

import BetakillerWampFacade from '@betakiller/wamp-wrapper';
import HtmlNodes from './HtmlNodes';
import Stopwatch from './Stopwatch';
import TestWampRpcTestResult from './TestWampRpcTestResult';

class TestWampRpcTest {
  constructor(rpcConnection) {
    this.wampConnection = null;
    this.rpcConnection = rpcConnection;

    this.testsRequestsQty = 0;
    this.testsResponsesQty = 0;
    this.testsErrorsQty = 0;
    this.testsTotalTime = 0;
    this.connectionTime = 0;
    this.abort = false;

    this.apiResource = 'WampTest';
    this.apiMethod = 'data';
    this.nodes = new HtmlNodes('.testWampRpcTest[data-template]');

    const rootNode = this.nodes.getRoot();

    this.apiDataCase = rootNode.attr('data-case');

    console.log('Test case value is ', rootNode.attr('data-case-return-value'));

    this.apiDataCaseValue = JSON.parse(rootNode.attr('data-case-return-value'));

    this.params = {
      'connectionType': rootNode.attr('data-connectionType'),
      'testsQty': parseInt(rootNode.attr('data-testsQty')),
      'qtyInPack': parseInt(rootNode.attr('data-qtyInPack')),
      'delayPack': parseInt(rootNode.attr('data-delayPack')),
    };

    this.stopwatch = new Stopwatch();

    this.result = new TestWampRpcTestResult(this)
      .markAsPerformed()
      .setConnectionType(this.params.connectionType)
      .setTestsQty(this.params.testsQty)
      .setConnectionTime(0);

    if (this.params.connectionType === 'wamp') {
      this._connectWamp();
    } else {
      this._runTests();
    }
  }

  stop() {
    this.abort = 1;
    this.result.markAsStopped();
    console.log('Test. Stopped.');
    return this;
  }

  _runTests() {
    console.log('Test. Qty tests:', this.params.testsQty);
    console.log('Test. Qty tests in pack:', this.params.qtyInPack);

    this.result.markAsPerformed();

    this.__runTests().then();

    if (this.abort) {
      console.log('Test. Run of tests was aborted');
    } else {
      console.log('Test. Run of tests was completed');
    }
  }

  async __runTests() {
    let packsQty = this.params.testsQty / this.params.qtyInPack;
    packsQty = Math.ceil(packsQty);
    if (isNaN(packsQty)) packsQty = 0;
    console.log('Test. Qty packs:', packsQty);
    if (packsQty < 1) return;

    for (let packIndex = 0; packIndex < packsQty; packIndex++) {
      if (this.abort) return;

      console.log('Test. Run pack:', (packIndex + 1), '/', packsQty);

      if (packIndex && this.params.delayPack) {
        console.log('Test. Pack delay:', this.params.delayPack);
        await this._sleep(this.params.delayPack);
      }

      for (let testIndex = 0; testIndex < this.params.qtyInPack; testIndex++) {
        if (this.abort) return;

        if (this.testsRequestsQty >= this.params.testsQty) break;

        let testId = 1 + testIndex + packIndex * this.params.qtyInPack;
        console.log('Test. Run test:', testId, '/', this.params.testsQty);

        this._request(testId);
      }
    }
  }

  _connectWamp() {
    this.abort = false;
    this.result
      .markAsConnecting()
      .incConnectionTry();

    try {
      this.stopwatch.start('wampConnection');

      this.wampConnection = new BetakillerWampFacade(
        () => this._onWampConnect(false),
        (data) => this._onWampConnect(true, data),
        true
      );
      this.wampConnection.connect();

    } catch (error) {
      this._onWampConnect(true, error);
    }
  }

  _onWampConnect(isError, data) {
    let connectionTime = this.stopwatch.getInterim('wampConnection');

    let reason = '';
    if (!isError) {
      reason = this._onWampConnectResolve();
      if (!this.connectionTime) {
        this.connectionTime = connectionTime;
        this.result.setConnectionTime(connectionTime);
      }
    } else {
      reason = this._onWampConnectReject(data);
    }

    if (reason) reason = ` (${reason})`;
    console.log(`Test. WAMP connection time${reason}:`, connectionTime);

    if (this.abort === 1) {
      this.abort = 2;
      if (this.wampConnection) {
        this.wampConnection.close();
      }
    }

    if (!this.abort && !isError) this._runTests();
  }

  _onWampConnectResolve() {
    console.log('Test. WAMP connected.');

    return 'open';
  }

  _onWampConnectReject(data) {
    let reason = 'error';
    let detailReason = 'unknown';
    let reconnectionState = 'unknown';
    let isClosedByClient = false;
    if (data.hasOwnProperty('reason')) {
      reason = data.reason;
      detailReason = data.detailReason;
      reconnectionState = data.reconnectionState;
      isClosedByClient = data.isClosedByClient;
    }

    if (isClosedByClient) {
      console.log('Test. WAMP connection closed by client');
    } else {
      if (data.hasOwnProperty('reason')) {
        console.error(
          `Test. WAMP connection. Error:`,
          `Reason "${reason}".`,
          `Detail reason "${detailReason}".`,
          `Data:`, data
        );
      } else {
        if (data instanceof Error) data = data.message;
        console.error('Test. WAMP connection. Error:', data);
      }

      console.log(`Test. WAMP connection reconnection "${reconnectionState}".`);
    }

    if (!isClosedByClient) {
      this.result.setError(`Connection. Reason: "${reason}". Detail reason "${detailReason}". Data: ${data}`);
    }
    if (!this.abort && reconnectionState) this.result.incConnectionTry();

    return reason;
  }

  _request(testId) {
    this.testsRequestsQty++;

    this
      .result
      .incRequestsQty()
      .updateRequestsProgress(this.testsRequestsQty, this.params.testsQty);

    this.stopwatch.start('test-' + testId);

    let apiData = {
      case: this.apiDataCase
    };

    console.log(
      `Test. Request:`,
      `Test id "${testId}".`,
      `API resource "${this.apiResource}".`,
      `API method "${this.apiMethod}".`,
      `API data:`, apiData
    );

    try {
      if (this.params.connectionType === 'wamp') {
        this.wampConnection
          .requestApi(this.apiResource, this.apiMethod, apiData)
          .then(response => this._onResponse(false, testId, response))
          .catch(error => this._onResponse(true, testId, error));

      } else {
        this
          .rpcConnection[this.apiResource][this.apiMethod]
          .apply(null, apiData)
          .done((response) => this._onResponse(false, testId, response))
          .fail((error) => this._onResponse(true, testId, error));
      }
    } catch (error) {
      this._onResponse(true, testId, error);
    }
  }

  _onResponse(isError, testId, responseData) {
    let testTime = this.stopwatch.stop('test-' + testId);
    console.log(`Test. Test "${testId}" time:`, testTime);
    console.log('Checking for response data to be', this.apiDataCaseValue);

    if (JSON.stringify(responseData) !== JSON.stringify(this.apiDataCaseValue)) {
      isError = true;
    }

    this.testsResponsesQty++;
    if (isError) this.testsErrorsQty++;
    this.testsTotalTime += testTime;
    let averageExecutionTime = this.testsTotalTime / this.testsRequestsQty;

    if (!isError) {
      console.log('Test. Request response:', responseData);
    } else {
      if (responseData instanceof Error) responseData = responseData.message;
      console.error('Test. Request error:', responseData);
    }

    this
      .result
      .incResponsesQty()
      .updateResponsesProgress(this.testsResponsesQty, this.params.testsQty)
      .setAverageExecutionTime(averageExecutionTime);

    if (isError) {
      this
        .result
        .incErrorsQty()
        .updateErrorsProgress(this.testsErrorsQty, this.params.testsQty)
        .setError('Response data: ' + JSON.stringify(responseData));
    }

    if (this.testsResponsesQty >= this.params.testsQty) {
      this.result.markAsCompleted();

      if (this.params.connectionType === 'wamp') {
        this.wampConnection.close();
      }
    }
  }

  _sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
  }
}

new TestWampRpcTest(undefined);
