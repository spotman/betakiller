'use strict';

import WampConnection from './WampConnection'

export class WampRequest {
  constructor(connection, resourcePrefix = '') {
    this.connection     = connection
    this.resourcePrefix = resourcePrefix
  }

  request(resource, method = '', data = undefined) {
    let url = []
    if (this.resourcePrefix) url.push(this.resourcePrefix)
    url.push(resource)
    if (method) url.push(method)
    url = url.join('.')

    this.connection
      .getSession()
      .call(url, data)
      .then(responce => this._onResolve(responce))
      .catch(message => this._onReject(url, message))

    return new Promise((resolve, reject) => {
      this.resolve = resolve
      this.reject  = reject
    })
  }

  _onResolve(responce) {
    this.resolve(responce)
  }

  _onReject(url, message) {
    this.reject(url, message)
  }
}
