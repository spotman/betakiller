'use strict';

class WampRequest {
  constructor(connection, resourcePrefix = '') {
    this.connection     = connection;
    this.resourcePrefix = resourcePrefix;
  }

  request(resource, method = '', data = undefined) {
    let url = [];
    if (this.resourcePrefix) url.push(this.resourcePrefix);
    url.push(resource);
    if (method) url.push(method);
    url = url.join('.');

    data = this._normalizeCallData(data);

    this.connection
      .getSession()
      .call(url, data)
      .then(response => this._onResolve(response))
      .catch(message => this._onReject(url, message));

    return new Promise((resolve, reject) => {
      this.resolve = resolve;
      this.reject  = reject;
    });
  }

  _onResolve(response) {
    this.resolve(response);
  }

  _onReject(url, message) {
    this.reject({'url': url, 'error': message,});
  }

  _normalizeCallData(data) {
    if (data === null || data === undefined) {
      return data;
    }

    if (data instanceof Array) {
      return data;
    }

    if (typeof data === 'object') {
      data = this._objectToArray(data);
    } else {
      data = [data];
    }

    return data;
  }

  _objectToArray(data) {
    return Object.keys(data).map(function (key) {
      return data[key];
    });
  }
}
