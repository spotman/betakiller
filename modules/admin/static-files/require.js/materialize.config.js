require.config({

  paths: {
    "materialize.global":         "../admin/components/materialize/js/global",
    "materialize.cards":          "../admin/components/materialize/js/cards",
    "materialize.forms":          "../admin/components/materialize/js/forms",
    "materialize.charCounter":    "../admin/components/materialize/js/character_counter",
    "materialize.sideNav":        "../admin/components/materialize/js/sideNav",
    "materialize.collapsible":    "../admin/components/materialize/js/collapsible",
    "materialize.waves":          "../admin/components/materialize/js/waves",
    "materialize.dropdown":       "../admin/components/materialize/js/dropdown",
    "materialize.animation":      "../admin/components/materialize/js/animation",
    "velocity":                   "../admin/components/materialize/js/velocity.min",
    "hammerjs":                   "../admin/components/materialize/js/hammer.min",
    "jquery.hammer":              "../admin/components/materialize/js/jquery.hammer",
    "jquery.easing":              "../admin/components/materialize/js/jquery.easing.1.3"
  },

  shim: {
    // 'materialize': {
    //   deps: ['jquery'] //'velocity', 'hammerjs'
    // },

    // 'jquery.hammer': {
    //   deps: ['jquery', 'hammerjs']
    // },

    'materialize.cards': {
      deps: ['jquery', 'materialize.global', 'velocity']
    },

    'materialize.forms': {
      deps: ['jquery', 'materialize.global', 'materialize.dropdown']
    },

    'materialize.charCounter': {
      deps: ['jquery']
    },

    'materialize.sideNav': {
      deps: ['jquery', 'jquery.hammer', 'velocity', 'materialize.collapsible']
    },

    'materialize.collapsible': {
      deps: ['jquery', 'materialize.animation']
    },

    'materialize.dropdown': {
      deps: ['jquery', 'materialize.animation']
    },

    'materialize.animation': {
      deps: ['jquery', 'jquery.easing']
    }
  }

});
