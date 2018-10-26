"use strict";

//const path = require('path');
//const root = path.resolve(__dirname);
const Encore            = require('@symfony/webpack-encore');
//const webpack           = require('webpack');
//const UglifyJsPlugin    = require('uglifyjs-webpack-plugin');
//const HardSourceWebpackPlugin = require('hard-source-webpack-plugin');

process.noDeprecation = true;

Encore
  .configureRuntimeEnvironment(process.env.NODE_ENV)

  // the project directory where all compiled assets will be stored
  .setOutputPath('dist/')

  // the public path used by the web server to access the previous directory
  .setPublicPath('/assets/static/widgets/language-selection/dist')

  .setManifestKeyPrefix('dist/')

  // will create public/build/app.js and public/build/app.css
  .addEntry('widget', './src/widget.js')

  .addEntry('vendor', [
    '@babel/polyfill',
    'jquery',
  ])

  // allow sass/scss files to be processed
  //.enableSassLoader()

  .enableSourceMaps(!Encore.isProduction())

  // empty the outputPath dir before each build
  .cleanupOutputBeforeBuild()

// show OS notifications when builds finish/fail
//.enableBuildNotifications()

//.addPlugin(new HardSourceWebpackPlugin)

;

/**
 * Workaround for trash vendor libraries which are not ready for uglify
 * @link https://github.com/symfony/webpack-encore/issues/139#issuecomment-323585179
 */
const webpackConfig = Encore.getWebpackConfig();

//if (Encore.isProduction()) {
//  // Remove the old version first
//  webpackConfig.plugins = webpackConfig.plugins.filter(
//    plugin => !(plugin instanceof webpack.optimize.UglifyJsPlugin)
//  );
//
//// Add the new one
//  webpackConfig.plugins.push(new UglifyJsPlugin());
//}

// export the final configuration
module.exports = webpackConfig;
