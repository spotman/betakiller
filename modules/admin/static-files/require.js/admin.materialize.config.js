require.config({

  paths: {
    "materialize": "../admin/components/materialize/dist/js/materialize.min",
  },

  shim: {
    'materialize': {
      deps: ['jquery'], // We use jQuery wrappers so we need jQuery for using MaterializeCSS
      exports: 'M'
    },
  }

});
