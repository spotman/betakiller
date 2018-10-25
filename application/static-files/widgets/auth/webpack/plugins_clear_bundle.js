"use strict";

const clean  = require('clean-webpack-plugin');
const config = require('./config.js');

module.exports = {
  plugins: [
    new clean(config.paths.bundle, {
      root:    config.paths.root,
      verbose: true,//todo ?
      dry:     false//todo ?
    }),
  ],
};

