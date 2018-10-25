'use strict';

import Widget from './widget.js';

export default class {
  constructor($container) {
    this.$container = $container;

    new Widget(this.$container, this.onReady, this.onSuccessful);
  }

  onReady() {

  }

  onSuccessful() {
    // Всё в порядке, перенаправляем пользователя
    location.reload(true);
  }
}
