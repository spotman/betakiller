<?php

return [

    'models' =>  [
        'StaticFiles'  =>  [
            'url_key' => 'static',
            'provider' => 'StaticFiles',
            // Allow any file type (no upload allowed in the model)
            'mimes' => true,

            'storage' => [
                'name' => 'LocalCfs',
                'path' => 'assets/static'
            ],
        ],
    ],

];
