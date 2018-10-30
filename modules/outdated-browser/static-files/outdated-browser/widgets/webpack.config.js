"use strict";

const Encore            = require('@symfony/webpack-encore');
const CopyWebpackPlugin = require('copy-webpack-plugin');

process.noDeprecation = true;

Encore
  .configureRuntimeEnvironment(process.env.NODE_ENV)

  // the project directory where all compiled assets will be stored
  .setOutputPath('dist/')

  // the public path used by the web server to access the previous directory
  .setPublicPath('/assets/static/outdated-browser/widgets/dist')

  .setManifestKeyPrefix('dist/')

  // will create public/build/app.js and public/build/app.css
  .addEntry('OutdatedBrowserWidget', [
    './src/OutdatedBrowserWidget.js',
  ])

  .addPlugin(
    new CopyWebpackPlugin([{
      from: 'node_modules/outdatedbrowser/outdatedbrowser/lang',
      to:   'lang',
    }, {
      from: 'node_modules/outdatedbrowser/outdatedbrowser/outdatedbrowser.min.js',
      to:   'outdatedbrowser.min.js',
    }, {
      from: 'node_modules/outdatedbrowser/outdatedbrowser/outdatedbrowser.min.css',
      to:   'outdatedbrowser.min.css',
    }])
  )

  //.createSharedEntry('vendor', [
  //  'outdatedbrowser',
  //])

  .enableSourceMaps(!Encore.isProduction())

  // empty the outputPath dir before each build
  .cleanupOutputBeforeBuild()

;

// export the final configuration
module.exports = Encore.getWebpackConfig();
