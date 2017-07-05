<?php

return [
    'providers' =>  [
        'ContentPostThumbnail'  =>  [
            'url_key' => 'content-post-thumbnails',
            'provider' => 'Image',

            'storage' => [
                'name' => 'Local',
                'path' => 'post-thumbnails'
            ],
        ],
        'ContentImage'  =>  [
            'url_key' => 'content-images',
            'provider' => 'Image',

            'storage' => [
                'name' => 'Local',
                'path' => 'content-images'
            ],
        ],

        'ContentAttachment'  =>  [
            'url_key' => 'content-attachments',
            'provider' => 'Attachment',

            'storage' => [
                'name' => 'Local',
                'path' => 'content-attachments'
            ],
        ]
    ],
];
