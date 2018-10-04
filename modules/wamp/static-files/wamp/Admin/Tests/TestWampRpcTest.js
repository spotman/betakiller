'use strict';

require([
  'jquery',
  'validation.api.rpc',
  'wamp/autobahn',
], function ($, rpc, autobahn) {
  window.autobahn = autobahn;

  class TestWampRpcTestController {
    constructor(rpcConnection) {
      this.wampUrl                    = 'wss://' + window.location.hostname + '/wamp';
      this.wampRealm                  = 'public';
      this.wampCookieSessionName      = 'sid';
      this.wampCookieSessionSeparator = '~';
      this.wampAuthSecret             = window.navigator.userAgent;
      this.wampConnection             = undefined;
      this.rpcConnection              = rpcConnection;

      this.testsRequestsQty  = 0;
      this.testsResponsesQty = 0;
      this.testsErrorsQty    = 0;
      this.testTotalTime     = 0;
      this.abort             = false;

      this.apiResource = 'validation';
      this.apiMethod   = 'userEmail';
      this.apiData     = ['qwe:testId@qew.qwe'];

      this.nodes  = new HtmlNodes('.testWampRpcTest[data-template]');
      this.params = {
        'connectionType': this.nodes.getRoot().attr('data-connectionType'),
        'testsQty':       parseInt(this.nodes.getRoot().attr('data-testsQty')),
        'qtyInPack':      parseInt(this.nodes.getRoot().attr('data-qtyInPack')),
        'delayPack':      parseInt(this.nodes.getRoot().attr('data-delayPack')),
      };

      this.stopwatch = new Stopwatch();

      this.result = new TestWampRpcTestResult(this)
        .markAsPerformed()
        .setConnectionType(this.params.connectionType)
        .setTestsQty(this.params.testsQty)
        .setConnectionTime(0);

      if (this.params.connectionType === 'wamp') {
        this.result.markAsConnecting();
        this._connectWamp();
      } else {
        this._runTests();
      }
    }

    stop() {
      this.abort = true;
      return this;
    }

    _runTests() {
      console.log('Qty tests:', this.params.testsQty);
      console.log('Qty tests in pack:', this.params.qtyInPack);

      this.result.markAsPerformed();

      this.__runTests().then();

      if (this.abort) {
        console.log('Run of tests was aborted');
      } else {
        console.log('Run of tests was completed');
      }

      this.result.markAsCompleted();

      if (this.params.connectionType === 'wamp') {
        this.wampConnection.close();
      }
    }

    async __runTests() {
      let packsQty = this.params.testsQty / this.params.qtyInPack;
      packsQty     = Math.ceil(packsQty);
      if (isNaN(packsQty)) packsQty = 0;
      console.log('Qty packs:', packsQty);
      if (packsQty < 1) return;

      for (let packIndex = 0; packIndex < packsQty; packIndex++) {
        if (this.abort) return;

        console.log('Run pack:', (packIndex + 1), '/', packsQty);

        if (packIndex && this.params.delayPack) {
          console.log('Pack delay:', this.params.delayPack);
          await this._sleep(this.params.delayPack);
        }

        for (let testIndex = 0; testIndex < this.params.qtyInPack; testIndex++) {
          if (this.abort) return;

          if (this.testsRequestsQty >= this.params.testsQty) break;

          let testId = 1 + testIndex + packIndex * this.params.qtyInPack;
          console.log('Run test:', testId, '/', this.params.testsQty);

          this._request(testId);
        }
      }
    }

    _connectWamp() {
      this.stopwatch.start('wampConnection');

      this.result.incConnectionTry();

      let wampCookieSession = new WampCookieSession(this.wampCookieSessionName, this.wampCookieSessionSeparator);
      let wampAuthChallenge = new WampAuthChallenge(wampCookieSession.getId(), this.wampAuthSecret);
      console.log(
        `WAMP connection:`,
        `Url "${this.wampUrl}".`,
        `Realm "${this.wampRealm}".`,
        `Cookie session name "${this.wampCookieSessionName}".`,
        `Cookie session separator "${this.wampCookieSessionSeparator}".`
      );
      try {
        this.wampConnection = new WampConnection(this.wampUrl, this.wampRealm, wampAuthChallenge);
        this.wampConnection
          .onOpen((connection) => this._onWampConnect(false, connection))
          .onClose((reason, details) => this._onWampConnect(true, {'reason': reason, 'details': details}))
          .open();
      } catch (error) {
        this._onWampConnecting(true, error);
      }
    }

    _onWampConnect(isError, data) {
      let connectionTime = this.stopwatch.stop('wampConnection');

      let reason = '';
      if (!isError) {
        reason = this._onWampConnectResolve(data);
      } else {
        reason = this._onWampConnectReject(data);
      }

      if (this.abort) this.wampConnection.close();

      if (reason) reason = ` (${reason})`;
      console.log(`WAMP connection time${reason}:`, connectionTime);

      this.result.setConnectionTime(connectionTime);

      if (!isError) this._runTests();
    }

    _onWampConnectResolve(data) {
      console.log('WAMP connection:', data);

      return 'open';
    }

    _onWampConnectReject(data) {
      let reason            = 'error';
      let reconnectionState = 'unknown';
      let isClosedByClient  = false;
      if (data.hasOwnProperty('reason')) {
        reason            = data.reason;
        reconnectionState = this.wampConnection.getDetailsReconnectionState(data.details);
        isClosedByClient  = this.wampConnection.isDetailsClosedByClient(data.details);
      }
      if (this.abort) reconnectionState = false;

      if (isClosedByClient) {
        console.log('WAMP connection closed by client');
      } else {
        if (data.hasOwnProperty('reason')) {
          console.error(
            `WAMP connection. Error:`,
            `Reason "${data.reason}".`,
            `Details:`, data.details
          );
        } else {
          if (data instanceof Error) data = data.message;
          console.error('WAMP connection. Error:', data);
        }

        console.log(`WAMP connection reconnection "${reconnectionState}".`);
      }

      if (reconnectionState) this.result.incConnectionTry();

      return reason;
    }

    _request(testId) {
      this.testsRequestsQty++;

      this
        .result
        .incRequestsQty()
        .updateRequestsProgress(this.testsRequestsQty, this.params.testsQty);

      this.stopwatch.start('test-' + testId);

      let apiData = this._createApiData(testId);
      console.log(
        `Request:`,
        `Test id "${testId}".`,
        `API resource "${this.apiResource}".`,
        `API method "${this.apiMethod}".`,
        `API data:`, apiData
      );
      if (this.params.connectionType === 'wamp') {
        new WampApiRequest(this.wampConnection)
          .request(this.apiResource, this.apiMethod, apiData)
          .then(response => this._onResponse(false, response, testId))
          .catch(error => this._onResponse(true, error, testId));

      } else {
        this
          .rpcConnection[this.apiResource][this.apiMethod]
          .apply(null, this._createApiData(testId))
          .done((response) => this._onResponse(false, response, testId))
          .fail((error) => this._onResponse(true, error, testId));
      }
    }

    _onResponse(isError, data, testId) {
      let testTime = this.stopwatch.stop('test-' + testId);
      console.log(`Test "${testId}" time:`, testTime);

      this.testsResponsesQty++;
      if (isError) this.testsErrorsQty++;
      this.testTotalTime += testTime;
      let averageExecutionTime = this.testTotalTime / this.testsRequestsQty;

      if (!isError) {
        console.log('Request response:', data);
      } else {
        if (data instanceof Error) data = data.message;
        console.error('Request error:', data);
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
          .updateErrorsProgress(this.testsErrorsQty, this.params.testsQty);
      }
    }

    _createApiData(testId) {
      return this.apiData.map((value) => {
        return value.replace(/:testId/ig, testId);
      });
    }

    _sleep(ms) {
      return new Promise(resolve => setTimeout(resolve, ms));
    }
  }

  new TestWampRpcTestController(rpc);

  return;
  let wamp = new WampFacade(true);
  try {
    wamp.close();
    wamp
      .request('api', ['validation', 'userEmail', 'qwe@qwe.qwe'])
      .then(response => {
        console.log('Request response:', response)
        wamp.close();

        wamp
          .requestApi('validation', 'userEmail', 'qwe2@qwe.qwe')
          .then(response => {
            console.log('Request response:', response)
          })
          .catch(error => console.error('Request error:', error));
      })
      .catch(error => console.error('Request error:', error));
  } catch (error) {
    console.error('Request system error:', error)
  }
});
