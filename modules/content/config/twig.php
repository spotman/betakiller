<?php

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
