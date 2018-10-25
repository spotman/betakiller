"use strict";

module.exports = {
  optimization: {
    splitChunks: {
      cacheGroups: {
        default: false,
        vendors: false,
        chunks:  {
          name:    'chunks',
          chunks:  'all',
          enforce: true,
        }
      }
    }
  },
};

