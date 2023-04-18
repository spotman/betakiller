<?php

use BetaKiller\Env\AppEnv;

$appEnv = AppEnv::instance();

$cachePath = $appEnv->isAppRunning()
    ? $appEnv->getCachePath('twig')
    : false;

return [

    /**
     * Twig Loader options
     */
    'loader'      => [
        'extension'  => 'twig',  // Extension for Twig files
        'path'       => 'views', // Path within cascading filesystem for Twig files

        /**
         * Enable caching of directories list
         */
        'cache'      => true,

        /**
         * Namespaces to add
         *
         *      'namespaces' => array(
         *          'templates' =>  'base/templates',
         *          'layouts'   =>  array('base/layouts', 'admin/layouts'),
         *      )
         */
        'namespaces' => [
            'layouts'   => 'layouts',
            'wrappers'  => 'wrappers',
            'ifaces'    => 'ifaces',
            'widgets'   => 'widgets',
            'templates' => 'templates',
            'macros'    => 'macros',

            'statics' => '../static-files',
        ],
    ],

    /**
     * Twig Environment options
     *
     * http://twig.sensiolabs.org/doc/api.html#environment-options
     */
    'environment' => [
        'auto_reload'      => false,
        'debug'            => false,
        'autoescape'       => 'html',
        'cache'            => $cachePath,
        'optimizations'    => -1,
        'strict_variables' => true,
    ],

    /**
     * Custom functions and filters
     *
     *      'functions' => array(
     *          'my_method' => array('MyClass', 'my_method'),
     *      ),
     */
    'functions'   => [
    ],

    'filters'    => [],

    /**
     * Twig extensions to register
     *
     *      'extensions' => array(
     *          'Twig_Extension_Debug',
     *          'MyProject_Twig_Extension'
     *      )
     */
    'extensions' => [
        \Twig\Extension\DebugExtension::class,
        \BetaKiller\TwigExtension::class,
        \BetaKiller\TwigCacheExtension::class,
        \Twig_Extensions_Extension_Text::class,
    ],

];
