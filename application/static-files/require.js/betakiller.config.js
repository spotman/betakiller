require.config({

    baseUrl: '/assets/static/require.js',

    paths: {
        'jquery':                   '../jquery/jquery-1.11.1.min',
        'jquery.utils':             '../jquery/jquery.utils',

        'twig':                     '../twig.js/twig.wrapper',
        'twig.original':            '../twig.js/twig',
        'twig.extensions':          '../twig.js/twig.extensions',

        'api.rpc':                  '../api/rpc.definition',
        'api.rpc.factory':          '../api/rpc.factory',
        'jquery.jsonRPC':           '../api/jquery.jsonRPC',

        'auth.login':               'views/auth/login',
        'auth.widget':              'views/auth/widget',
        'auth.modal':               'views/auth/modal',
        'auth.provider.regular':    'views/auth/providers/regular',
        'auth.provider.uLogin':     'views/auth/providers/uLogin',
    },

    waitSeconds: 30

});
