"use strict";

module.exports = {
  entry: {
    'common':                                         [
      '@babel/polyfill',
      'jquery',
      'popper.js',
      'bootstrap',
      'bootstrap_styles',
      'fontawesome',
    ],
    'auth': [
      './auth.js',
      './providers/regular.js',
      './providers/uLogin.js',
    ],
  },
};
