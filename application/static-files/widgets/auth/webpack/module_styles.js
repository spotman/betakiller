"use strict";

const miniCssExtract = require('mini-css-extract-plugin');
const config         = require('./config.js');

module.exports = {
  module: {
    rules: [{
      test: /\.((s*)css|sass)$/,
      use:  [
        //
        {
          loader:  'style-loader',
          options: {
            sourceMap: true,
          }
        },
        //
        {
          loader: miniCssExtract.loader,
        },
        //
        {
          loader:  'css-loader',
          options: {
            sourceMap:     true,
            importLoaders: 1,// 0 => no loaders (default); 1 => postcss-loader; 2 => postcss-loader, sass-loader
          }
        },
        //
        {
          loader:  'postcss-loader',
          options: {
            sourceMap: true,
            plugins:   [
              require('precss'),//sass in css
              require('postcss-preset-env'),//polyfills, autoprefixer
            ]
          }
        },
        //
        {
          loader:  'sass-loader',
          options: {
            sourceMap:    true,
            includePaths: [
              config.paths.src + '/scss',
            ],
          }
        },
      ]
    }]
  },
};

