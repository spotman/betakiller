<?php defined('SYSPATH') OR die('No direct script access.');

return array(

	/**
	 * Twig Loader options
	 */
	'loader' => array(
        /**
         * Namespaces to add
         *
         *      'namespaces' => array(
         *          'templates' =>  'base/templates',
         *          'layouts'   =>  array('base/layouts', 'admin/layouts'),
         *      )
         */
        'namespaces'    =>  array(
            'notifications' =>  'notifications',
        ),
	),

);
