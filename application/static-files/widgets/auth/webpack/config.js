"use strict";

const path     = require('path');
const pathRoot = path.resolve(__dirname, '../');
const paths    = {
  'root':    pathRoot,
  'webpack': pathRoot + '/webpack',
  'src':     pathRoot + '/src',
  'bundle':  pathRoot + '/bundle',
  'public':  '/assets/static/widgets/auth/bundle/',
};

module.exports = {
  'paths':   paths,
  'require': (name) => require(paths.webpack + '/' + name + '.js'),
};

