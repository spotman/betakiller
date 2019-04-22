'use strict';

import ProviderRegular from './providers/regular.js';

const providers = {
  "regular": ProviderRegular
};

class Auth {
  constructor() {
    this.initProviders();
  }

  initProviders() {
    const $el = document.getElementById('widget-auth');

    $el.getAttribute('data-providers').split(',')
      .forEach((providerName) => {
        const provider = providers[providerName];
        new provider(function () {
          location.replace(location.href);
        });
      });
  }
}

new Auth();
