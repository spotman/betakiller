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
    const $el         = document.getElementById('widget-auth'),
          redirectUrl = $el.getAttribute('data-redirect') || location.href;

    if ($el.hasAttribute('data-initialized')) {
      return;
    }

    //console.log('Redirect to', redirectUrl);

    $el.getAttribute('data-providers').split(',')
      .forEach((providerName) => {
        const provider = providers[providerName];
        new provider(function () {
          location.replace(redirectUrl);
        });
      });

    $el.setAttribute('data-initialized', 'true');
  }
}

new Auth();
