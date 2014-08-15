require.config({

    baseUrl: '{# assets_base_url #}require.js',

    shim: {
        'jquery.typeahead': {
            deps:                   ['jquery'],
            exports:                '$.fn.typeahead'
        }
    },

    paths: {
        'jquery':                   '../jquery/jquery-1.11.1.min',
        'jquery.utils':             '../jquery/utils',
        'jquery.ui.widget':         '../jquery/ui/jquery.ui.widget',

        'jquery.typeahead':         '../jquery/typeahead/typeahead.bundle.min',

        'waypoints':                '../jquery/waypoints/waypoints.min',
        'waypoints.sticky':         '../jquery/waypoints/waypoints-sticky.min',

        'wookmark':                 '../jquery/jquery.wookmark.min',

        'jquery.fileupload':        '../jquery/fileupload/jquery.fileupload',
        'jquery.iframe-transport':  '../jquery/fileupload/jquery.iframe-transport',
        'jquery.fileupload.bundle': '../jquery/fileupload/jquery.fileupload.bundle',

        'jquery.scrollTo':          '../jquery/scrollTo/jquery.scrollTo.min',

        'jquery.x-editable':        '../jquery/bootstrap3-editable/jquery.x-editable.bundle',
        'jquery.select2.bootstrap': '../jquery/select2/jquery.select2.bootstrap',

        'imagesLoaded':             '../imagesLoaded/imagesloaded.pkgd.min',
        'load-image':               '../load-image/load-image',

        'underscore':               '../underscore/underscore-min',
        'backbone':                 '../backbone/backbone',
        'backbone.rpc':             '../backbone/backbone.rpc',
        'geppetto':                 '../backbone/backbone.geppetto',
        'backbone.subroute':        '../backbone/backbone.subroute',

        'twig':                     '../twig.js/twig.wrapper',
        'twig.original':            '../twig.js/twig',

        'api.rpc':                  '../api/rpc.definition',
        'api.rpc.factory':          '../api/rpc.factory',
        'jquery.jsonRPC':           '../api/jquery.jsonRPC',

        'ansi_up':                  '../ansi_up/ansi_up',

        'auth.login':               'views/auth/login',
        'auth.widget':              'views/auth/widget',
        'auth.modal':               'views/auth/modal',
        'auth.provider.regular':    'views/auth/providers/regular',
        'auth.provider.uLogin':     'views/auth/providers/uLogin',

        /**
         * Кастомные контролы
         */
        'comboBox':                 './views/comboBox'
    },

    waitSeconds: 30

});
