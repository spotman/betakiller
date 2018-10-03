'use strict';

class WampApiRequest {
  constructor(connection) {
    this.request = new WampRequest(connection);
  }

  call(resource, method, data = null) {
    let params = [resource, method];
    data = this._normalizeCallData(data);

    return this.request.request('api', params.concat(data));
  }

  _normalizeCallData(data) {
    if (data === null || data === undefined) {
      return [];
    }

    if (data instanceof Array) {
      return data;
    }

    if (typeof data === 'object') {
      return this._objectToArray(data);
    }

    return [data];
  }

  _objectToArray(data) {
    return Object.keys(data).map(function (key) {
      return data[key];
    })
  }
}
