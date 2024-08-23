<?php

use BetaKiller\Env\AppEnv;

return [

    /**
     * Twig Loader options
     */
    'loader'      => [
        'extension'  => 'html',  // Extension for Twig files
        'path'       => 'twigs', // Path within cascading filesystem for Twig files

        /**
         * Enable caching of directories list
         */
        'cache'      => AppEnv::instance()->inProductionMode(),

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
            'templates' => 'templates',
        ],

        'prototype_namespace' => 'proto',
    ],

    /**
     * Twig Environment options
     *
     * http://twig.sensiolabs.org/doc/api.html#environment-options
     */
    'environment' => [
        'auto_reload'         => AppEnv::instance()->inDevelopmentMode(),
        'debug'               => AppEnv::instance()->inDevelopmentMode(),
        'autoescape'          => true,
        'base_template_class' => Twig_Template::class,
        'cache'               => null,
        'chmod'               => 0755,
        'charset'             => 'utf-8',
        'optimizations'       => -1,
        'strict_variables'    => false,
    ],

    /**
     * Custom functions, filters and tests
     *
     *      'functions' => array(
     *          'my_method' => array('MyClass', 'my_method'),
     *      ),
     */
    'functions'   => [],
    'filters'     => [],
    'tests'       => [],

    /**
     * Twig extensions to register
     *
     *      'extensions' => array(
     *          'Twig_Extension_Debug',
     *          'MyProject_Twig_Extension'
     *      )
     */
    'extensions'  => [],

];
