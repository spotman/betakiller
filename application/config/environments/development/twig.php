<?php

return [

    /**
     * Twig Loader options
     */
    'loader'      => [
        /**
         * Enable caching of directories list
         */
        'cache' => false,
    ],

    /**
     * Twig Environment options
     *
     * http://twig.sensiolabs.org/doc/api.html#environment-options
     */
    'environment' => [
	    // Dev stations may have different users for CLI and HTTP server, so group access is required
        'chmod'               => 0775,

        'auto_reload' => true, // !Kohana::inProduction(TRUE),
        'debug'       => true, // !Kohana::inProduction(),
    ],

    /**
     * Twig extensions to register
     *
     *      'extensions' => array(
     *          'Twig_Extension_Debug',
     *          'MyProject_Twig_Extension'
     *      )
     */
    'extensions'    =>  [
        \Kint\Twig\TwigExtension::class
    ],
];
