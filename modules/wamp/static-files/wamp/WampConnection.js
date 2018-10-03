'use strict';

class WampConnection {
  constructor(url, realm, authChallenge = undefined) {
    this.url           = url;
    this.realm         = realm;
    this.authChallenge = authChallenge;
    this._markAsNotReady();
  }

  _markAsNotReady() {
    this.wampConnection  = null;
    this.wampSession     = null;
    this.connectionReady = false;
    this.connectionFirst = true;
  }

  _markAsReady() {
    this.connectionFirst = !this.connectionReady;
    this.connectionReady = true;
  }

  _ErrorNotReady() {
    return new Error('Connection not ready. Use connect() or wait for connection complete.');
  }

  isOnProgress() {
    return this.wampConnection && !this.connectionReady;
  }

  isReady() {
    return this.connectionReady;
  }

  isFirst() {
    if (!this.isReady()) {
      throw this._ErrorNotReady();
    }
    return this.connectionFirst;
  }

  getConnection() {
    if (!this.isReady()) {
      throw this._ErrorNotReady();
    }
    return this.wampConnection;
  }

  getSession() {
    if (!this.isReady()) {
      throw this._ErrorNotReady();
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
    return new Promise(() => {
      let options = {
        url:   this.url,
        realm: this.realm,
      };
      if (this.authChallenge instanceof WampAuthChallenge) {
        options.authmethods = [this.authChallenge.getMethod()];
        options.authid      = this.authChallenge.getAuthId();
        options.onchallenge = (...args) => this._onChallenge.apply(this, args);
      }
      this.wampConnection         = new autobahn.Connection(options);
      this.wampConnection.onopen  = (...args) => this._onOpen.apply(this, args);
      this.wampConnection.onclose = (...args) => this._onClose.apply(this, args);
      this.wampConnection.open();
    });
  }

  _onResolve() {
    this.resolve(this);
  }

  _onOpen(session/*, details*/) {
    this.wampSession = session;
    this._markAsReady();
    this._onResolve();
  }

  _onChallenge(session, method, extra) {
    try {
      return this.authChallenge.run(session, method, extra);
    } catch (e) {
      throw new Error('Unable authenticate. Error: ' + e.message);
    }
  }

  disconnect(reason, message) {
    if (!this.isReady()) {
      throw this._ErrorNotReady();
    }
    this.wampConnection.close(reason, message);
    return this;
  }

  _onClose(status, reason) {
    if (status === 'lost') return;
    this._markAsNotReady();
    throw new Error('Connection closed. Status: ' + status + '. Reason: ' + reason);
  }
}
