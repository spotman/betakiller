require.config({

    baseUrl: '{staticfiles_url}require.js',
    paths: {
        // the left side is the module ID,
        // the right side is the path to
        // the jQuery file, relative to baseUrl.
        // Also, the path should NOT include
        // the '.js' file extension. This example
        // is using jQuery 1.9.0 located at
        // js/lib/jquery-1.9.0.js, relative to
        // the HTML page.
        'jquery':           '../jquery/jquery-1.10.2',
        'jquery.utils':     '../jquery/utils',

        'underscore':         '../underscore/underscore',
        'backbone':           '../backbone/backbone',
        'backbone.rpc':       '../backbone/backbone.rpc'
    } //,

    //Remember: only use shim config for non-AMD scripts,
    //scripts that do not already call define(). The shim
    //config will not work correctly if used on AMD scripts,
    //in particular, the exports and init config will not
    //be triggered, and the deps config will be confusing
    //for those cases.
//    shim: {
//        ,
//        'foo': {
//            deps: ['bar'],
//            exports: 'Foo',
//            init: function (bar) {
//                //Using a function allows you to call noConflict for
//                //libraries that support it, and do other cleanup.
//                //However, plugins for those libraries may still want
//                //a global. "this" for the function will be the global
//                //object. The dependencies will be passed in as
//                //function arguments. If this function returns a value,
//                //then that value is used as the module export value
//                //instead of the object found via the 'exports' string.
//                //Note: jQuery registers as an AMD module via define(),
//                //so this will not work for jQuery. See notes section
//                //below for an approach for jQuery.
//                return this.Foo.noConflict();
//            }
//        }
//    }

});
