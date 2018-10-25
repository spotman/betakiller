'use strict';

import Provider_regular from './providers/regular.js';
import Provider_uLogin from './providers/uLogin.js';
const providers = {
  Provider_regular,
  Provider_uLogin
}

class Auth {
  constructor() {
    this.$widget = $('.widget-auth');
    this.initProviders();
  }

  initProviders() {
    this.$widget.data('providers').split(',')
      .forEach((providerName) => {
        let providerClass = `Provider_${providerName}`;
        new providers[providerClass](this.onSuccess)
      });
  }

  onSuccess() {
    location.reload(true);
  }
}

new Auth();
