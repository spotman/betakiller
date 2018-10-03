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
      'public',
      wampAuthChallenge
    );
    wampConnection
      .connect()
      .then(connection => {
        console.log('OK. WAMP connection. ', connection);
        this.WampOnOpen(connection);
      })
      .catch(message => console.log('ER. WAMP connection. ', message));

    this.WampOnOpen = (connection) => {
      //if (!connection.isFirst()) return;

      new WampRequest(wampConnection, 'api')
        .request('validation', 'userEmail', 'qwe22@qwe.qwe')
        .then(response => console.log('OK. WAMP request. Response: ', response))
        .catch(message => console.log('ER. WAMP request. Url "' + message.url + '". Error: ', message.error));
    };

  });
});
