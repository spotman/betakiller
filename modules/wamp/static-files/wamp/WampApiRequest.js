'use strict';

class WampApiRequest extends WampRequest {
  constructor(connection) {
    super(connection);
    this.method  = '';
    this.data    = [];
  }

  request(resurce, method, data = undefined) {
    data = super._normalizeCallData(data);
    data.unshift(method)
    data.unshift(resurce)
    return super.request('api', data);
  }
}
