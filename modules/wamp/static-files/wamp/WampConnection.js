'use strict';

class WampConnection {
  constructor(url, realm, authChallenge = undefined) {
    this.url           = url;
    this.realm         = realm;
    this.authChallenge = authChallenge;
  }

  isOnProgress() {
    //todo
    return this.hasOwnProperty('wampConnection');
  }

  isReady() {
    return this.hasOwnProperty('connectionReady');
  }

  isFirst() {
    if (!this.isReady()) {
      throw this._onNotReady();
    }
    return this.connectionFirst;
  }

  getConnection() {
    if (!this.isOnProgress()) {
      throw this._onNotReady();
    }
    return this.wampConnection;
  }

  getSession() {
    if (!this.isReady()) {
      throw this._onNotReady();
    }
    return this.wampSession;
  }

  close(reason, message) {
    this.wampConnection.close(reason, message);
    return this;
  }

  connect() {
    if (this.isOnProgress() || this.isReady()) {
      throw this._onProgress();
    }
    this._connect();
    return new Promise((resolve, reject) => {
      this.resolve = resolve;
      this.reject  = reject;
    });
  }

  _connect() {
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
  }

  _markConnectionAsReady() {
    this.connectionFirst = !this.hasOwnProperty('connectionReady');
    this.connectionReady = true;
  }

  _onNotReady() {
    return new Error('Connection not ready. Use connect() or wait for connection complete.');
  }

  _onProgress() {
    return new Error('Connection in progress or already ready.');
  }

  _onResolve() {
    this.resolve(this);
  }

  _onReject(message) {
    this.reject(message);
  }

  _onOpen(session/*, details*/) {
    this.wampSession = session;
    this._markConnectionAsReady();
    this._onResolve();
  }

  _onClose(status, reason) {
    if (status !== 'closed') return;
    if (!reason) {
      reason = 'unknown';
    } else {
      reason = reason.reason;
    }
    this._onReject('Status "' + status + '". Reason "' + reason + '".');
  }

  _onChallenge(session, method, extra) {
    if (this.authChallenge instanceof WampAuthChallenge) {
      try {
        return this.authChallenge.run(session, method, extra);
      } catch (e) {
        let message = 'Authenticate challenge error: ' + e.message;
        this._onReject(message);
      }
    } else {
      throw new Error('Authenticate challenge not found or invalid instance. Valid instance "WampAuthChallenge".');
    }
  }
}
