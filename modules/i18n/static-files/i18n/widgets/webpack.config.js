"use strict";

const Encore = require('@symfony/webpack-encore');

process.noDeprecation = true;

Encore
  .configureRuntimeEnvironment(process.env.NODE_ENV)

  // the project directory where all compiled assets will be stored
  .setOutputPath('dist/')

  // the public path used by the web server to access the previous directory
  .setPublicPath('/assets/static/widgets/i18n/dist')

  .setManifestKeyPrefix('dist/')

  // will create public/build/app.js and public/build/app.css
  .addEntry('ChangeLanguageWidget', './src/ChangeLanguageWidget.js')

  .addEntry('ChangeLanguageWidgetVendor', [
    '@babel/polyfill',
    'jquery',
  ])

  .enableSourceMaps(!Encore.isProduction())

  // empty the outputPath dir before each build
  .cleanupOutputBeforeBuild()
;

// export the final configuration
module.exports = Encore.getWebpackConfig();
