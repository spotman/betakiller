<?php defined('SYSPATH') OR die('No direct script access.');

return array(

    'path'  =>  realpath(APPPATH.'..'.DIRECTORY_SEPARATOR.'sites'),

    'sites' =>  array(

        'kohana.local'  =>  array(
            'urls'      =>  array('kohana.local', '*.kohana.local')
        ),

    ),

);