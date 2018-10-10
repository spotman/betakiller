"use strict";

const encore       = require('@symfony/webpack-encore');
const webpack      = require('webpack');
const uglifyPlugin = require('uglifyjs-webpack-plugin');

// ?
process.noDeprecation = true;

encore
  .setOutputPath('build/')
  .setPublicPath('/build')
  .setManifestKeyPrefix('build/')
  .addEntry('bundle-manager', './TestWampRpcManager.js')
  .addEntry('bundle-test', './TestWampRpcTest.js')
  .autoProvidejQuery()
  .enableSourceMaps(!encore.isProduction())
  .cleanupOutputBeforeBuild()
  .enableBuildNotifications()
;

const webpackConfig = encore.getWebpackConfig();
if (encore.isProduction()) {
  webpackConfig.plugins = webpackConfig.plugins.filter(
    plugin => !(plugin instanceof webpack.optimize.UglifyJsPlugin)
  );
  webpackConfig.plugins.push(new uglifyPlugin());
}

module.exports = webpackConfig;
