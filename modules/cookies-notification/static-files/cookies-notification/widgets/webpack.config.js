"use strict";

const Encore            = require('@symfony/webpack-encore');
const CopyWebpackPlugin = require('copy-webpack-plugin');

process.noDeprecation = true;

Encore
  .configureRuntimeEnvironment(process.env.NODE_ENV)

  // the project directory where all compiled assets will be stored
  .setOutputPath('dist/')

  // the public path used by the web server to access the previous directory
  .setPublicPath('/assets/static/cookies-notification/widgets/dist')

  .setManifestKeyPrefix('dist/')

  // will create public/build/app.js and public/build/app.css
  .addEntry('CookiesNotificationWidget', [
    './src/CookiesNotificationWidget.js',
  ])

  .addPlugin(
    new CopyWebpackPlugin([{
      from: 'node_modules/cookieconsent/build/cookieconsent.min.js',
      to:   'cookieconsent.min.js',
    }, {
      from: 'node_modules/cookieconsent/build/cookieconsent.min.css',
      to:   'cookieconsent.min.css',
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
