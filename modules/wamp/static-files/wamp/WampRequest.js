'use strict';

class WampRequest {
  constructor(connection) {
    this.connection     = connection
  }

  request(procedure, data = null) {
    data = this._normalizeCallData(data)

    this.connection
      .getSession()
      .call(procedure, data)
      .then(response => this._onResolve(response))
      .catch(message => this._onReject(procedure, message))

    return new Promise((resolve, reject) => {
      this.resolve = resolve
      this.reject  = reject
    })
  }

  _onResolve(response) {
    this.resolve(response)
  }

  _onReject(url, message) {
    this.reject({'url': url, 'error': message,})
  }

  _normalizeCallData(data) {
    if (data === null || data === undefined) {
      return data
    }

    if (data instanceof Array) {
      return data
    }

    if (typeof data === 'object') {
      data = this._objectToArray(data)
    } else {
      data = [data]
    }

    return data
  }

  _objectToArray(data) {
    return Object.keys(data).map(function (key) {
      return data[key]
    })
  }
}
