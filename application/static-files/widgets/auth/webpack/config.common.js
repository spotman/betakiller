"use strict";

const config = require('./config.js');
const merge  = require('webpack-merge');

module.exports = merge([
  config.require('context'),
  config.require('resolve_alias'),
  config.require('entry'),
  config.require('output'),
  config.require('devtool'),
  config.require('module_js'),
  config.require('module_styles'),
  config.require('module_images'),
  config.require('module_fonts'),
  config.require('plugins_css_extract'),
  config.require('plugins_clear_bundle'),
  config.require('plugins_provide_popper'),
  config.require('optimization_minimize'),
]);


