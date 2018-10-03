'use strict';

class WampConnection {
  constructor(url, realm, authChallenge = undefined) {
    this.url           = url;
    this.realm         = realm;
    this.authChallenge = authChallenge;
    this._markAsNotReady();
  }

  _markAsNotReady() {
    this.wampConnection  = undefined;
    this.wampSession     = undefined;
    this.connectionReady = false;
    this.resolve         = undefined;
    this.reject          = undefined;
  }

  _markAsReady() {
    this.connectionReady = true;
  }

  _errorNotReady() {
    return new Error('Connection not ready. Use connect() or wait for connection complete.');
  }

  isOnProgress() {
    return this.wampConnection && !this.connectionReady;
  }

  isReady() {
    return this.connectionReady;
  }

  getConnection() {
    if (!this.isReady()) {
      throw this._errorNotReady();
    }
    return this.wampConnection;
  }

  getSession() {
    if (!this.isReady()) {
      throw this._errorNotReady();
    }
    return this.wampSession;
  }

  connect() {
    if (this.isOnProgress()) {
      throw new Error('Connection on progress.');
    }
    if (this.isReady()) {
      throw new Error('Connection already ready.');
    }

    let options = {
      url:   this.url,
      realm: this.realm,
    };
    if (this.authChallenge instanceof WampAuthChallenge) {
      options.authmethods = [this.authChallenge.getMethod()];
      options.authid      = this.authChallenge.getAuthId();
      options.onchallenge = (session, method, extra) => this._onChallenge(session, method, extra);
    }
    this.wampConnection         = new autobahn.Connection(options);
    this.wampConnection.onopen  = (session, details) => this._onOpen(session, details);
    this.wampConnection.onclose = (status, reason) => this._onClose(status, reason);
    this.wampConnection.open();

    return new Promise((resolve, reject) => {
      this.resolve = resolve;
      this.reject  = reject;
    });
  }

  _onOpen(session/*, details*/) {
    this.wampSession = session;
    this._markAsReady();
    this.resolve(this);
  }

  _onClose(status, reason) {
    if (status === 'lost') return;
    this._markAsNotReady();
    throw new Error('Connection closed. Status "' + status + '". Reason: ' + reason);
  }

  _onChallenge(session, method, extra) {
    try {
      return this.authChallenge.run(session, method, extra);
    } catch (error) {
      throw new Error('Unable authenticate. Error: ' + error.message);
    }
  }
}
