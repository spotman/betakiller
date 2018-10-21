<?php defined('SYSPATH') or die('No direct script access.');

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
        'cache'      => Kohana::$environment === Kohana::PRODUCTION,

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
        'auto_reload'         => Kohana::$environment === Kohana::DEVELOPMENT,
        'debug'               => Kohana::$environment === Kohana::DEVELOPMENT,
        'autoescape'          => true,
        'base_template_class' => Twig_Template::class,
        'cache'               => TWIGPATH.'cache',
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
