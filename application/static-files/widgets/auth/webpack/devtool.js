"use strict";

const isDev = process.env.NODE_ENV !== 'production';

module.exports = {
  devtool: isDev ? 'source-map' : 'nosources-source-map',// enable/disable global
};


