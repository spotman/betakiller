'use strict';

class WampRequest {
  constructor(connection) {
    this.connection = connection;
  }

  request(procedure, data = undefined) {
    data = this._normalizeCallData(data);
    return new Promise((resolve, reject) => {
      this.connection
        .getSession()
        .call(procedure, data)
        .then(response => resolve(response))
        .catch(error => reject({'procedure': procedure, 'data': data, 'message': error}));
    });
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
