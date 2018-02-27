"use strict";

let path = require('path');
let Encore = require('@symfony/webpack-encore');
let HardSourceWebpackPlugin = require('hard-source-webpack-plugin');

process.noDeprecation = true;

Encore
// the project directory where all compiled assets will be stored
  .setOutputPath('dist/')

  // the public path used by the web server to access the previous directory
  .setPublicPath('/assets/static/shortcode-manager/dist')

  .setManifestKeyPrefix('build/')

  // will create public/build/app.js and public/build/app.css
  .addEntry('app', './src/main.js')

  .createSharedEntry('vendor', [
    'jquery',
    'api.rpc.factory',
    'content.api.rpc',
    'vue'
  ])

  // allow sass/scss files to be processed
  .enableSassLoader()

  .enableVueLoader()

  // allow legacy applications to use $/jQuery as a global variable
  //.autoProvidejQuery()

  .enableSourceMaps(!Encore.isProduction())

  // empty the outputPath dir before each build
  .cleanupOutputBeforeBuild()

  // show OS notifications when builds finish/fail
  .enableBuildNotifications()

  // create hashed filenames (e.g. app.abc123.css)
  //.enableVersioning(false)

  .addAliases({
    'jquery': path.resolve(__dirname, '../../../../modules/admin/static-files/admin/node_modules/jquery/src/jquery.js'),
    'jquery.jsonRPC': path.resolve(__dirname, '../../../../application/static-files/api/jquery.jsonRPC.js'),
    'api.rpc.factory': path.resolve(__dirname, '../../../../application/static-files/api/rpc.factory.js'),
    'content.api.rpc': path.resolve(__dirname, '../require.js/content.api.rpc.js'),
  })

  .addPlugin(new HardSourceWebpackPlugin)

//.addExternals({})
;

//console.log(path.resolve(__dirname, '../require.js/content.api.rpc.js'));

// export the final configuration
module.exports = Encore.getWebpackConfig();
