require([
  'jquery',
  'validation.api.rpc',
  'autobahn.min',
], function ($, rpc, autobahn) {
  $(function () {
    window.autobahn = autobahn;

    let session           = new Session('sid', '~');
    let wampAuthChallenge = new WampAuthChallenge(session.getId(), window.navigator.userAgent);
    let wampConnection    = new WampConnection(
      'wss://' + window.location.hostname + '/wamp',
      'realm1',
      wampAuthChallenge
    );
    wampConnection
      .connect()
      .then(connection => {
        console.log('OK. WAMP connection. ', connection);
        this.doWampRequest();
      })
      .catch(message => console.log('ERROR. WAMP connection. ', message));

    this.doWampRequest = () => {
      let wampRequest = new WampRequest(wampConnection, 'api');
      wampRequest
        .request('validation', 'userEmail', 'qwe22@qwe.qwe')
        .then(response => console.log('OK. WAMP request. Response: ', response))
        .catch(message => console.log('ERROR. WAMP request. Url: ' + message.url + '. Error: ', message.error));
    };

  });
});
