<?php defined('SYSPATH') OR die('No direct script access.');

return array(

	/**
	 * Twig Loader options
	 */
	'loader' => array(
		'extension' => 'twig',  // Extension for Twig files
		'path'      => 'views', // Path within cascading filesystem for Twig files

        /**
         * Enable caching of directories list
         */
        'cache' =>  Kohana::in_production(TRUE),

        /**
         * Namespaces to add
         *
         *      'namespaces' => array(
         *          'templates' =>  'base/templates',
         *          'layouts'   =>  array('base/layouts', 'admin/layouts'),
         *      )
         */
        'namespaces'    =>  array(
            'layouts'   =>  'layouts',
            'wrappers'  =>  'wrappers',
            'ifaces'    =>  'ifaces',
            'widgets'   =>  'widgets',
            'templates' =>  'templates',
            'macros'    =>  'macros',

            'statics'   =>  '../static-files',
        ),
	),

	/**
	 * Twig Environment options
	 *
	 * http://twig.sensiolabs.org/doc/api.html#environment-options
	 */
	'environment' => array(
		'auto_reload'         => ! Kohana::in_production(TRUE),
		'debug'               => ! Kohana::in_production(),
		'autoescape'          => TRUE,
		'base_template_class' => 'Twig_Template',
        'cache'               => MultiSite::instance()->site_path().DIRECTORY_SEPARATOR.'twig-cache',
		'charset'             => 'utf-8',
		'optimizations'       => -1,
		'strict_variables'    => TRUE,
	),

	/**
	 * Custom functions and filters
	 *
	 *      'functions' => array(
	 *          'my_method' => array('MyClass', 'my_method'),
	 *      ),
	 */
	'functions' => array(
        '__'    =>  '__'
    ),

	'filters' => array(),

    /**
     * Twig extensions to register
     *
     *      'extensions' => array(
     *          'Twig_Extension_Debug',
     *          'MyProject_Twig_Extension'
     *      )
     */
    'extensions'    =>  array(
        \Twig_Extension_Debug::class,
        \BetaKiller_Twig_Extension::class,
        \Twig_CacheExtension::class,
        \Twig_Extensions_Extension_Text::class,
    ),

);
