require([
  'jquery',
  'validation.api.rpc',
  'wamp/autobahn.min',
], function ($, rpc, autobahn) {
  window.autobahn = autobahn;

  class TestWampRpcTest {
    constructor(rpcConnection) {
      this.wampUrl                    = 'wss://' + window.location.hostname + '/wamp';
      this.wampRealm                  = 'public';
      this.wampCookieSessionName      = 'sid';
      this.wampCookieSessionSeparator = '~';
      this.wampAuthSecret             = window.navigator.userAgent;
      this.wampConnection             = undefined;
      this.rpcConnection              = rpcConnection;

      this.apiResource = 'validation';
      this.apiMethod   = 'userEmail';
      this.apiData     = ['qwe[:testId]@qew.qwe'];

      this.nodes             = new HtmlNodes('.testWampRpcTest[data-template]');
      this.connectionType    = this.nodes.getRoot().attr('data-connectionType');
      this.params            = {
        'testQty':     parseInt(this.nodes.getRoot().attr('data-testQty')),
        'countInPack': parseInt(this.nodes.getRoot().attr('data-countInPack')),
        'delayPack':   parseInt(this.nodes.getRoot().attr('data-delayPack')),
      };
      this.testsRequestsQty  = 0;
      this.testsResponsesQty = 0;
      this.testsErrorsQty    = 0;
      this.testTotalTime     = 0;
      this.abort             = false;
      this.stopwatch         = new Stopwatch();
      //this.result            = new Result()
      //  .markAsPerformed()
      //  .setConnectionType(this.params.connectionType)
      //  .setTestQty(this.params.testQty)
      //  .setConnectionTime(0);

      if (this.connectionType === 'wamp') {
        this._connectWamp();
      } else {
        this._runTests();
      }
    }

    _runTests() {
      console.log('Qty tests:', this.params.testQty);
      console.log('Qty tests in pack:', this.params.countInPack);

      this.__runTests().then();

      if (this.abort) {
        console.log('Run of tests was aborted');
      } else {
        console.log('Run of tests was completed');
      }

      //this
      //  .result
      //  .markAsCompleted();
    }

    async __runTests() {
      let packsQty = this.params.testQty / this.params.countInPack;
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

        for (let testIndex = 0; testIndex < this.params.countInPack; testIndex++) {
          if (this.abort) return;

          if (this.testsRequestsQty >= this.params.testQty) break;

          let testId = 1 + testIndex + packIndex * this.params.countInPack;
          console.log('Run test:', testId, '/', this.params.testQty);

          this._request(testId);
        }
      }
    }

    _connectWamp() {
      this.stopwatch.start('wampConnection');

      let wampCookieSession = new WampCookieSession(this.wampCookieSessionName, this.wampCookieSessionSeparator);
      let wampAuthChallenge = new WampAuthChallenge(wampCookieSession.getId(), this.wampAuthSecret);
      console.log(
        'WAMP connecting.',
        'Url', '"' + this.wampUrl + '".',
        'Realm', '"' + this.wampRealm + '".',
        'Cookie session name', '"' + this.wampCookieSessionName + '".',
        'Cookie session separator', '"' + this.wampCookieSessionSeparator + '".'
      );
      new WampConnection(this.wampUrl, this.wampRealm, wampAuthChallenge)
        .connect()
        .then(connection => this._onWampConnecting(false, connection))
        .catch(error => this._onWampConnecting(true, error));
    }

    _onWampConnecting(isError, data) {
      let connectionTime = this.stopwatch.stop('wampConnection');
      console.log('WAMP connection time:', connectionTime);

      if (!isError) {
        console.log('WAMP connection:', data);
        this.wampConnection = data;
      } else {
        if (data instanceof Error) data = data.message;
        console.error('WAMP connection:', data);
      }

      //this.result.setConnectionTime(connectionTime);

      if (!isError) this._runTests();
    }

    _request(testId) {
      this.testsRequestsQty++;

      //this
      //  .result
      //  .incRequestsQty()
      //  .updateRequestsProgress(this.testsRequestsQty, this.params.testQty);

      this.stopwatch.start('test-' + testId);

      let apiData = this._createApiData(testId);
      console.log(
        'Request.',
        'Test id', '"' + testId + '".',
        'API resource', '"' + this.apiResource + '".',
        'API method', '"' + this.apiMethod + '".',
        'API data:', apiData
      );
      if (this.connectionType === 'wamp') {
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
      console.log('Test "' + testId + '" time:', testTime);

      this.testsResponsesQty++;
      if (isError) this.testsErrorsQty++;
      this.testTotalTime += testTime;
      let averageExecutionTime = this.testTotalTime / this.testsRequestsQty;

      if (!isError) {
        console.log('Response:', data);
      } else {
        console.error('Response:', data);
      }

      //this
      //  .result
      //  .incResponsesQty()
      //  .updateResponsesProgress(this.testsResponsesQty, this.params.testQty)
      //  .setAverageExecutionTime(averageExecutionTime);

      if (isError) {
        //this
        //  .result
        //  .incErrorsQty()
        //  .updateErrorsProgress(this.testsErrorsQty, this.params.testQty);
      }
    }

    _createApiData(testId) {
      return this.apiData.map((value) => {
        return value.replace('[:testId]', testId);
      });
    }

    _sleep(ms) {
      return new Promise(resolve => setTimeout(resolve, ms));
    }
  }

  new TestWampRpcTest(rpc);
});
