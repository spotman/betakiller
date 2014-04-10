require.config({

    baseUrl: '{# assets_base_url #}require.js',
    paths: {
        'jquery':                   '../jquery/jquery-1.10.2',
        'jquery.utils':             '../jquery/utils',
        'jquery.ui.widget':         '../jquery/ui/jquery.ui.widget',

        'jquery.fileupload':        '../jquery/fileupload/jquery.fileupload',
        'jquery.iframe-transport':  '../jquery/fileupload/jquery.iframe-transport',
        'jquery.fileupload.bundle': '../jquery/fileupload/jquery.fileupload.bundle',

        'load-image':               '../load-image/load-image',

        'underscore':               '../underscore/underscore',
        'backbone':                 '../backbone/backbone',
        'backbone.rpc':             '../backbone/backbone.rpc',
        'geppetto':                 '../backbone/backbone.geppetto',
        'backbone.subroute':        '../backbone/backbone.subroute',

        'twig':                     '../twig.js/twig.wrapper',
        'twig.original':            '../twig.js/twig',

        'api.rpc':                  '../api/rpc.definition',
        'jquery.jsonRPC':           '../api/jquery.jsonRPC',

        /**
         * Кастомные контролы
         */
        'comboBox':                 './views/comboBox'
    }

});
