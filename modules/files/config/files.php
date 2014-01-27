<?php defined('SYSPATH') OR die('No direct script access.');

return array(

    // List of all storage engines with default config for each other
    'storage'   =>  array(


        'CFS'   =>  array(

            // Base path for searching files in Kohana Cascade FileSystem
            'path'     =>  'files',

        ),

    ),

    // Fulfill this in your app config
    'providers' =>  array(

        // Provider for assets (CSS/JS/IMG)
        'Assets'    =>  array(

            // Fulfill this in concrete provider config
            'mime_types'    => array(
                'text/css',
                'application/javascript',
            ),

            'storage'       =>  array(
                'CFS'       =>  array(
                    'path'  =>  'assets'
                )
            ),

        ),

    ),

);