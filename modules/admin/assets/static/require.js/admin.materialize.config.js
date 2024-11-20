require.config({

  paths: {
    "materialize": "../admin/node_modules/materialize-css/dist/js/materialize.min",
  },

  shim: {
    'materialize': {
      deps: ['jquery'], // We use jQuery wrappers so we need jQuery for using MaterializeCSS
      exports: 'M'
    },
  }

});
