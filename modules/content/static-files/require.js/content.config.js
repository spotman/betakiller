/**
 * @link https://stackoverflow.com/questions/41886238/how-to-use-ckeditor-with-requirejs-and-the-r-js-optimizer
 * @type {string}
 */
var CKEDITOR_BASEPATH = '/assets/static/ckeditor/';

require.config({

  paths: {
    // Used in CKEditor for autosave plugin
    "moment": "../content/node_modules/moment/moment",
    'ckeditor': "../ckeditor/ckeditor",
    'ckeditor.jquery': "../ckeditor/adapters/jquery",

    // Images and gallery
    "fancybox": "../content/node_modules/@fancyapps/fancybox/dist/jquery.fancybox.min",
    "slick": "../content/node_modules/slick-carousel/slick/slick.min",

    "fancybox.config": "../content/fancybox.config",
  },

  shim: {
    "slick": {
      deps: ['jquery'], // Slick requires jQuery
    },
    "fancybox": {
      deps: ['jquery'], // Fancybox requires jQuery
    },
    "ckeditor": {
      deps: ['jquery'],
      exports: 'CKEDITOR'
    },
    "ckeditor.jquery": {
      deps: ['jquery'],
    }
  }

});
