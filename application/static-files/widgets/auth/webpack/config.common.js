"use strict";

const config = require('./config.js');
const merge  = require('webpack-merge');

module.exports = merge([
  config.require('context'),
  config.require('entry_common'),
  config.require('output'),
  config.require('devtool'),
  config.require('module_js'),
  config.require('plugins_clear_bundle'),
  config.require('optimization_minimize'),
]);


