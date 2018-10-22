<?php

return [

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
