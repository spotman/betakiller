<?php

return [

    // TODO Remove and replace with twig template
    'require.js'     =>  [
        'js'        =>  ['require.js/require.js', 'require.js/betakiller.config.js'],
    ],

    'models' =>  [
        'StaticFiles'  =>  [
            'url_key' => 'static-files',
            'provider' => 'StaticFiles',
            // Allow any file type (no upload allowed in the model)
            'mimes' => true,

            'storage' => [
                'name' => 'LocalCfs',
                'path' => 'static-files'
            ],
        ],
    ],

];
