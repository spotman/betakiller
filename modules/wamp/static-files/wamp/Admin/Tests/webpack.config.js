"use strict";

const glob         = require('glob');
const encore       = require('@symfony/webpack-encore');
const webpack      = require('webpack');
const uglifyPlugin = require('uglifyjs-webpack-plugin');

// ?
process.noDeprecation = true;

var entryItems = glob.sync('./*.js');
Array.prototype.push.apply(
  entryItems,
  glob.sync('./betakiller-wamp-js/src/*.js')
);
Array.prototype.diff = function (a) {
  return this.filter(function (i) {
    return a.indexOf(i) < 0;
  });
};
entryItems           = entryItems.diff([
  './webpack.config.js',
  './TestWampRpcManager.js'
]);
entryItems = [
  './TestWampRpcTest.js'
];
console.log(entryItems);

encore
  .setOutputPath('build/')
  .setPublicPath('/build')
  .setManifestKeyPrefix('build/')
  .addEntry('bundle', entryItems)
  //.autoProvidejQuery()
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
