require.config({

    baseUrl: '/assets/static/require.js',

    paths: {
        'jquery.utils':             '../jquery/jquery.utils',

        'twig':                     '../twig.js/twig.wrapper',
        'twig.original':            '../twig.js/twig',
        'twig.extensions':          '../twig.js/twig.extensions',

        'api.rpc':                  '../api/rpc.definition',
        'api.rpc.factory':          '../api/rpc.factory',
        'jquery.jsonRPC':           '../api/jquery.jsonRPC',
    },

    waitSeconds: 30

});
