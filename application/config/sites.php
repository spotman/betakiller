<?php defined('SYSPATH') OR die('No direct script access.');

return array(

    'path'  =>  realpath(APPPATH.'..'.DIRECTORY_SEPARATOR.'sites'),

    'sites' =>  array(

        'app-test'  =>  array(
            'urls'      =>  array('kohana.local', 'kohana.test.spotman.ru')
        ),

        'sub-domain-app-test'  =>  array(
            'urls'      =>  array('*.kohana.local', '*.kohana.test.spotman.ru')
        ),

    ),

);