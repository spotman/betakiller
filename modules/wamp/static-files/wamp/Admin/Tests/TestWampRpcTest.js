require([
  'jquery',
  'validation.api.rpc',
  'autobahn.min',
], function ($, rpc, autobahn) {
  $(function () {
    window.autobahn = autobahn

    let session           = new Session('sid', '~')
    let wampAuthChallenge = new WampAuthChallenge(session.getId(), window.navigator.userAgent)
    let wampConnection    = new WampConnection(
      'wss://' + window.location.hostname + '/wamp',
      'public',
      wampAuthChallenge
    )
    wampConnection
      .connect()
      .catch(message => console.log('ERROR. WAMP connection: ', message))
      .then(connection => {
        console.log('OK. WAMP connection: ', connection)
        if (!connection.isFirst()) return

        (new WampApiRequest(wampConnection))
          .call('validation', 'userEmail', 'qwe@qwe.qwe')
          .then(response => console.log('OK. WAMP request: ', response))
          .catch(message => console.log('ERROR. WAMP request: ', message.url, message.error))
      })

  });
});
