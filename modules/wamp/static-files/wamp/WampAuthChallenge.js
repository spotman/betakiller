'use strict';

class WampAuthChallenge {
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
        return this._run(session, method, extra)

      default:
        throw new Error('Unknown method "' + method + '". Valid method "' + this.method + '"')
    }
  }

  _run(session, method, extra) {
    var key = this.secretKey
    if (typeof extra.salt !== 'undefined') {
      key = autobahn.auth_cra.derive_key(key, extra.salt)
    }
    return autobahn.auth_cra.sign(key, extra.challenge)
  }
}
