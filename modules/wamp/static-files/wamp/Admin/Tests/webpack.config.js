"use strict";

const webpack  = require('webpack');
const path     = require('path');
const paths    = {
  'src':  __dirname,
  'dist': path.resolve(__dirname, 'bundle')
};
module.exports = {
  entry:   {
    'manager': paths.src + '/TestWampRpcManager.js',
    'test':    paths.src + '/TestWampRpcTest.js'
  },
  output:  {
    filename: '[name].js',
    path:     paths.dist
  },
  module:  {
    rules: [
      {
        test:    /\.js$/,
        loader:  'babel-loader',
        options: {
          ignore:  [],
          presets: [
            [
              "@babel/preset-env",
              {
                forceAllTransforms: true
              }
            ]
          ]
        },
      }
    ]
  },
  plugins: [
    new webpack.ProvidePlugin({
      '$':      'jquery',
      'jQuery': 'jquery'
    })
  ]
};

