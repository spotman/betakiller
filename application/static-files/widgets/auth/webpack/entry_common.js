"use strict";

module.exports = {
  entry: {
    'auth': [
      '@babel/polyfill',
      './auth.js',
      './providers/regular.js',
      './providers/uLogin.js',
    ],
  },
};
