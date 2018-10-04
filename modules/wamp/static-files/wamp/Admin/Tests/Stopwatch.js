'use strict';

class Stopwatch {
  constructor() {
    this.items = {};
  }

  start(name) {
    this.items[name] = {
      'start': performance.now(),
      'stop':  -1,
    };
    return this;
  }

  _getItem(name) {
    if (!this.items.hasOwnProperty(name)) {
      throw new Error(`Not found item "${name}".`);
    }
    return this.items[name];
  }

  stop(name) {
    return this.items[name].stop = this.getInterim(name);
  }

  getInterim(name) {
    return performance.now() - this._getItem(name).start;
  }

  get(name) {
    let item = this._getItem(name);
    if (item.stop < 0) {
      throw new Error(`Item "${name}" not stopped.`);
    }
  }
}
