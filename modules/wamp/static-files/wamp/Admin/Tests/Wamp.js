export class Wamp{

  constructor(sessionCookieName = 'session') {
    this.name = name;
  }

  connection() {
    this.sessionCookieName = 'session'
    this.callbackDone = false
    this.connect = function (callback) {
      this.connection = new autobahn.Connection({
        url: 'wss://spa.dev.worknector.com/wamp',
        realm: 'realm1',
        authmethods: ['wampcra'],
        authid: this.getCookie(this.sessionCookieName).replace(/.+?~(.+)/, '$1'),
        onchallenge: this.onChallenge
      })

      this.connection.onopen = function (_this, callback) {
        return function (session, details) {
          console.log('Wamp connecting')
          _this.session = session
          if (!_this.callbackDone) {
            _this.callbackDone = true
            if (typeof callback === 'function') callback(_this, session)
          }
        }
      }(this, callback)

      this.connection.open()

      return this
    }
    this.onChallenge = function (session, method, extra) {
      if (method === 'wampcra') {
        var keyToUse = window.navigator.userAgent
        if (typeof extra.salt !== 'undefined') {
          keyToUse = autobahn.auth_cra.derive_key(keyToUse, extra.salt)
        }
        return autobahn.auth_cra.sign(keyToUse, extra.challenge)
      } else {
        throw "don't know how to authenticate using '" + method + "'"
      }
    }
    this.getConnection = function () {
      if (!this.hasOwnProperty('connection')) throw 'Not found WAMP connection'
      return this.connection
    }
    this.getSession = function () {
      if (!this.hasOwnProperty('session')) throw 'Not found WAMP session'
      return this.session
    }
    this.getCookie = function (name) {
      var matches = document.cookie.match(new RegExp(
        "(?:^|; )" + name.replace(/([.$?*|{}()\[\]\\\/+^])/g, '\\$1') + "=([^;]*)"
      ));
      return matches ? decodeURIComponent(matches[1]) : undefined;
    }
  }

  WampRequest(session) {
    this.session = session
    this.execute = function (resource, method, arguments, callbackDone, callbackError) {
      this
        .session
        .call('api.' + resource + '.' + method, arguments)
        .then(
          function (callbackDone) {
            return function (result) {
              if (typeof callbackDone === 'function') callbackDone(result)
            }
          }(callbackDone),
          function (callbackError) {
            return function (error) {
              if (typeof callbackError === 'function') callbackError(error)
            }
          }(callbackError)
        )
      return this
    }
  }

}
