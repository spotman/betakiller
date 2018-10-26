'use strict';

import $ from 'jquery';
import Provider_regular from './providers/regular.js';

const providers = {
  Provider_regular
};

class Auth {
  constructor() {
    this.$widget = $('.widget-auth');
    this.initProviders();
  }

  initProviders() {
    this.$widget.data('providers').split(',')
      .forEach((providerName) => {
        let providerClass = `Provider_${providerName}`;
        new providers[providerClass](this.onSuccess);
      });
  }

  onSuccess() {
    location.reload(true);
  }
}

new Auth();
