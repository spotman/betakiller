'use strict';

import WampAuthChallenge from './WampAuthChallenge'

export default class WampConnection {
  constructor(url, realm, authChallenge = undefined) {
    this.url           = url
    this.realm         = realm
    this.authChallenge = authChallenge
  }

  isOnProgress() {
    return this.hasOwnProperty('wampConnection')
  }

  isReady() {
    return this.hasOwnProperty('connectionReady')
  }

  isFirst() {
    if (!this.isReady()) {
      throw this._onNotReady()
    }
    return this.connectionFirst
  }

  getConnection() {
    if (!this.isOnProgress()) {
      throw this._onNotReady()
    }
    return this.wampConnection
  }

  getSession() {
    if (!this.isReady()) {
      throw this._onNotReady()
    }
    return this.wampSession
  }

  connect() {
    if (this.isReady()) {
      this._onResolve()
    } else {
      this._connect()
    }
    return new Promise((resolve, reject) => {
      this.resolve = resolve
      this.reject  = reject
    })
  }

  _connect() {
    let options = {
      url:   this.url,
      realm: this.realm,
    }
    if (this.authChallenge instanceof WampAuthChallenge) {
      options.authmethods = [this.authChallenge.getMethod()]
      options.authid      = this.authChallenge.getAuthId()
      options.onchallenge = this._onChallenge
    }
    this.wampConnection        = new autobahn.Connection(options)
    this.wampConnection.onopen = this._onOpen
  }

  _markConnectionAsReady() {
    this.connectionFirst = !this.hasOwnProperty('connectionReady');
    this.connectionReady = true
  }

  _onNotReady() {
    return new Error('Connection not ready. Use connection() or wait for connection complete.')
  }

  _onResolve() {
    this.resolve(this)
  }

  _onReject(message) {
    this.reject(message)
  }

  _onOpen(session/*, details*/) {
    this.wampSession = session
    this._markConnectionAsReady()
    this._onResolve()
  }

  _onChallenge(session, method, extra) {
    this.authChallenge
      .run(session, method, extra)
      .catch(message => this._onReject(message))
  }
}
