require.config({

  paths: {
    // Used in CKEditor for autosave plugin
    "moment": "../content/node_modules/moment/moment",

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
    }
  }

});
