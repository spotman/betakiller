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
        'auto_reload' => true, // !Kohana::inProduction(TRUE),
        'debug'       => true, // !Kohana::inProduction(),
    ],
];
