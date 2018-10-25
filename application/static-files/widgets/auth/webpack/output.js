"use strict";

const config = require('./config.js');
const isDev  = process.env.NODE_ENV !== 'production';

module.exports = {
  output: {
    filename:      '[name].js',
    chunkFilename: '[name].js',
    path:          config.paths.bundle,
    publicPath:    config.paths.public,
    pathinfo:      isDev,
  },
};

