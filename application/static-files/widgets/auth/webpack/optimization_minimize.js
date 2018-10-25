"use strict";

const isDev = process.env.NODE_ENV !== 'production';

module.exports = {
  optimization: {
    minimize: isDev,
  },
};

