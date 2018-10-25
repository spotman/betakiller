"use strict";

module.exports = {
  module: {
    rules: [{
      test: /\.(woff|woff2|eot|ttf|otf)$/,
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
            outputPath: './fonts/'
          }
        }
      ]
    }]
  },
};

