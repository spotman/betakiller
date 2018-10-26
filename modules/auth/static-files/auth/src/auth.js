'use strict';

import $ from 'jquery';
import ProviderRegular from './providers/regular.js';

const providers = {
  "regular": ProviderRegular
};

class Auth {
  constructor() {
    this.$widget = $('.widget-auth');
    this.initProviders();
  }

  initProviders() {
    this.$widget.data('providers').split(',')
      .forEach((providerName) => {
        let provider = providers[providerName];
        new provider(function () {
          location.reload(true);
        });
      });
  }
}

new Auth();
