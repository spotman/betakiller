"use strict";

const miniCssExtract = require('mini-css-extract-plugin');
const isDev          = process.env.NODE_ENV !== 'production';

module.exports = {
  plugins: [
    new miniCssExtract({
      filename: isDev ? '[name].css' : '[name].[hash].css',
    }),
  ],
};

