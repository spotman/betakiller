'use strict';

export class Session {
  constructor(cookieName, cookieSeparator = '') {
    this.cookieName      = cookieName
    this.cookieSeparator = cookieSeparator
  }
  getId() {
    this.cookieName      = cookieName
    this.cookieSeparator = cookieSeparator
  }
}
