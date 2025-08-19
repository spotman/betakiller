<?php

use Cherif\InertiaPsr15\Twig\InertiaExtension;

return [

    /**
     * Custom functions and filters
     *
     *      'functions' => array(
     *          'my_method' => array('MyClass', 'my_method'),
     *      ),
     */
    'functions' => [],

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
        InertiaExtension::class,
    ],

];
