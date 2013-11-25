<?php defined('SYSPATH') OR die('No direct script access.');

return array(

	/**
	 * Twig Loader options
	 */
	'loader' => array(
		'extension' => 'twig',  // Extension for Twig files
		'path'      => 'views', // Path within cascading filesystem for Twig files
	),

	/**
	 * Twig Environment options
	 *
	 * http://twig.sensiolabs.org/doc/api.html#environment-options
	 */
	'environment' => array(
		'auto_reload'         => (Kohana::$environment == Kohana::DEVELOPMENT),
		'debug'               => (Kohana::$environment == Kohana::DEVELOPMENT),
		'autoescape'          => TRUE,
		'base_template_class' => 'Twig_Template',
		'cache'               => TWIGPATH.'cache',
		'charset'             => 'utf-8',
		'optimizations'       => -1,
		'strict_variables'    => TRUE,
	),

	/**
	 * Custom functions and filters
	 *
	 *     'functions' => array(
	 *         'my_method' => array('MyClass', 'my_method'),
	 *     ),
	 */
	'functions' => array(
        '__'    =>  '__'
    ),

	'filters' => array(),

    /**
     * Twig extensions to register
     *
     * 'extensions' => array(
     *      'Twig_Extension_Debug',
     *      'MyProject_Twig_Extension'
     * )
     */
    'extensions'    =>  array(
        'Twig_Extension_Debug',
        'BetaKiller_Twig_Extension'
    ),

);
