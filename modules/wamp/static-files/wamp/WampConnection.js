'use strict';

class WampConnection {
  constructor(url, realm, authChallenge = undefined) {
    this.url           = url;
    this.realm         = realm;
    this.authChallenge = authChallenge;
    this.reconnect     = true;
    this.callbacks     = {
      'open':  undefined,
      'close': undefined,
    };
    this.errors        = {
      'notReady':   'Connection not ready. Use connect() or wait for connection complete.',
      'onProgress': 'Connection on progress.',
    };
    this._markAsNotReady();
  }

  getDetailsReconnectionState(details) {
    if (details.hasOwnProperty('will_retry')) {
      return details.will_retry;
    }
    return false;
  }

  getDetailsReconnectionTry(details) {
    if (details.hasOwnProperty('retry_count')) {
      return details.retry_count;
    }
    return 0;
  }

  getDetailsReconnectionDelay(details) {
    if (details.hasOwnProperty('retry_delay')) {
      return details.retry_count;
    }
    return 0;
  }

  isDetailsClosedByClient(details) {
    if (details.hasOwnProperty('reason')) {
      return details.reason === 'wamp.error.goodbye_and_out';
    }
    return false;
  }

  _markAsNotReady() {
    this.connection      = undefined;
    this.session         = undefined;
    this.connectionReady = false;
  }

  _markAsReady() {
    this.connectionReady = true;
  }

  _enableReconnect() {
    this.reconnect = true;
  }

  _disableReconnect() {
    this.reconnect = false;
  }

  _isReconnectEnabled() {
    return this.reconnect === true;
  }

  onOpen(callback) {
    this.callbacks.open = callback;
    return this;
  }

  onClose(callback) {
    this.callbacks.close = callback;
    return this;
  }

  isOnProgress() {
    return this.connection && !this.connectionReady;
  }

  isReady() {
    return this.connectionReady;
  }

  getConnection() {
    if (!this.isReady()) {
      throw new Error(this.errors.notReady);
    }
    return this.connection;
  }

  getSession() {
    if (!this.isReady()) {
      throw new Error(this.errors.notReady);
    }
    return this.session;
  }

  open() {
    if (this.isOnProgress()) {
      throw new Error(this.errors.onProgress);
    }
    if (this.isReady()) {
      throw new Error('Connection already opened.');
    }

    this._enableReconnect();

    let options = {
      url:   this.url,
      realm: this.realm,
    };
    if (this.authChallenge instanceof WampAuthChallenge) {
      options.authmethods = [this.authChallenge.getMethod()];
      options.authid      = this.authChallenge.getAuthId();
      options.onchallenge = (session, method, extra) => this._onChallenge(session, method, extra);
    }
    this.connection         = new autobahn.Connection(options);
    this.connection.onopen  = (session, details) => this._onOpen(session, details);
    this.connection.onclose = (reason, details) => this._onClose(reason, details);
    this.connection.open();

    return this;
  }

  close() {
    this._disableReconnect();
    if (this.isReady()) {
      this.connection.close('close_by_client', 'Closed by client.');
    }
    this._markAsNotReady();
    return this;
  }

  _onOpen(session/*, details*/) {
    this.session = session;
    this._markAsReady();
    if (typeof this.callbacks.open === 'function') {
      this.callbacks.open(this);
    }
  }

  _onClose(reason, details) {
    if (typeof this.callbacks.close === 'function') {
      this.callbacks.close(reason, details);
    }
    // if true then autobahn not be reconnecting
    return !this._isReconnectEnabled();
  }

  _onChallenge(session, method, extra) {
    try {
      return this.authChallenge.run(session, method, extra);
    } catch (error) {
      this._onClose('closed', `Unable authenticate. Error: ${error.message}`);
    }
  }
}
