'use strict';

export default class WampAuthChallenge {
  constructor(authId, secretKey) {
    this.method    = 'wampcra'
    this.authId    = authId
    this.secretKey = secretKey
  }

  getMethod() {
    return this.method
  }

  getAuthId() {
    return this.authId
  }

  run(session, method, extra) {
    switch (method) {
      case this.method:
        this._run(session, method, extra)
        break

      default:
        throw new Error('Unknown method "' + method + '". Valid method "' + this.method + '"')
    }
    return new Promise((resolve, reject) => {
      this.resolve = resolve
      this.reject  = reject
    })
  }

  _run(session, method, extra) {
    var key = window.navigator.userAgent
    if (typeof extra.salt !== 'undefined') {
      key = autobahn.auth_cra.derive_key(this.secretKey, extra.salt)
    }
    // todo execute auth error
    autobahn.auth_cra.sign(key, extra.challenge)
    this._onResolve()
  }

  _onResolve() {
    this.resolve(this)
  }

  _onReject(message) {
    this.reject(message)
  }
}
