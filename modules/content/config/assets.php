<?php
use BetaKiller\Assets\Handler\SaveEntityItemRelationHandler;

return [
    'providers' =>  [
        'ContentPostThumbnail'  =>  [
            'url_key' => 'content-post-thumbnails',
            'provider' => 'Image',
            'mimes' => [
                'image/jpeg',
                'image/png',
                'image/gif',
            ],
            'post_upload' => [
                SaveEntityItemRelationHandler::CODENAME,
            ],

            'deploy' => true,

            'storage' => [
                'name' => 'Local',
                'path' => 'post-thumbnails'
            ],
        ],
        'ContentImage'  =>  [
            'url_key' => 'content-images',
            'provider' => 'Image',

            'mimes' => [
                'image/jpeg',
                'image/png',
                'image/gif',
            ],

            'post_upload' => [
                SaveEntityItemRelationHandler::CODENAME,
            ],
            'deploy' => true,

            'storage' => [
                'name' => 'Local',
                'path' => 'content-images'
            ],
        ],

        'ContentAttachment'  =>  [
            'url_key' => 'content-attachments',
            'provider' => 'Attachment',

            // Allow any mime-type to be uploaded
            'mimes' => true,
            'post_upload' => [
                SaveEntityItemRelationHandler::CODENAME,
            ],

            'deploy' => true,

            'storage' => [
                'name' => 'Local',
                'path' => 'content-attachments'
            ],
        ]
    ],
];
