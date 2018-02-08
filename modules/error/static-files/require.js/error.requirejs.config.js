require.config({

  paths: {
    "jquery.iframe-auto-height": "../error/node_modules/jquery-iframe-auto-height/dist/jquery-iframe-auto-height.min",
    "jquery.browser": "../error/node_modules/jquery-iframe-auto-height/release/jquery.browser"
  },

  shim: {
    "jquery.iframe-auto-height": {
      deps: ["jquery", "jquery.browser"]
    },
    "jquery.browser": {
      deps: ["jquery"]
    }
  }

});
