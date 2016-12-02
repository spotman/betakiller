<?php defined('SYSPATH') OR die('No direct script access.');

return array(

    /**
     * Twig extensions to register
     *
     *      'extensions' => array(
     *          'Twig_Extension_Debug',
     *          'MyProject_Twig_Extension'
     *      )
     */
    'extensions'    =>  array(
        \BetaKiller\Content\TwigExtension::class,
    ),

);
