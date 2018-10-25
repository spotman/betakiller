"use strict";

const webpack = require('webpack');
const config  = require('./config.js');

module.exports = {
  plugins: [
    new webpack.ProvidePlugin({
      'Popper': 'popper.js',
    }),
  ],
};

