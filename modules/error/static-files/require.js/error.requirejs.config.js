require.config({

  paths: {
    "iframe-resizer": "../error/node_modules/iframe-resizer/js/iframeResizer.min"
  },

  shim: {
    "iframe-resizer": {
      deps: ["jquery"],
      exports: "iFrameResize"
    },
  }

});
