<?php

return [

    /**
     * Twig Loader options
     */
    'loader'      => [
        /**
         * Enable caching of directories list
         */
        'cache' => true,
    ],

    /**
     * Twig Environment options
     *
     * http://twig.sensiolabs.org/doc/api.html#environment-options
     */
    'environment' => [
        'auto_reload' => false, // !Kohana::inProduction(TRUE),
        'debug'       => false, // !Kohana::inProduction(),
    ],

];
