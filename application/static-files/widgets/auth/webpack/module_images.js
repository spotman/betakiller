"use strict";

module.exports = {
  module: {
    rules: [{
      test: /\.(jpe?g|png|gif|svg)$/,
      use:  [
        // packing to base64
        //{
        //  loader:  'url-loader',
        //  options: {
        //    limit: 16384//16KB
        //  }
        //},
        //
        {
          loader:  'file-loader',
          options: {
            name:       '[name].[ext]',
            outputPath: './images/',
          }
        },
      ]
    }]
  }
};

