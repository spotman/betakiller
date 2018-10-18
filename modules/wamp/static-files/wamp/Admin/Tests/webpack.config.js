"use strict";

const webpack     = require('webpack');
const path        = require('path');
const paths       = {
  'src':  path.resolve(__dirname),
  'dist': path.resolve(__dirname, 'bundle')
};
const entryGlobal = ['@babel/polyfill'];
module.exports    = {
  entry:   {
    'manager': [].concat(
      entryGlobal,
      paths.src + '/TestWampRpcManager.js'
    ),
    'test':    [].concat(
      entryGlobal,
      paths.src + '/TestWampRpcTest.js'
    ),
  },
  output:  {
    filename: '[name].js',
    path:     paths.dist
  },
  module:  {
    rules: [{
      test:    /\.js$/,
      loader:  'babel-loader',
      options: {
        ignore:  [],
        presets: [[
          "@babel/preset-env",
          {
            debug:              true,
            forceAllTransforms: true
          }
        ]]
      }
    }]
  },
  plugins: [
    new webpack.ProvidePlugin({
      '$':      'jquery',
      'jQuery': 'jquery'
    })
  ]
};

