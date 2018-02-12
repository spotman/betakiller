<?php
use BetaKiller\Assets\Handler\SaveEntityItemRelationHandler;

return [
    'models' =>  [
        'ContentPostThumbnail'  =>  [
            'url_key' => 'post-thumbnails',
            'provider' => 'Image',
            'mimes' => [
                'image/jpeg',
                'image/png',
                'image/gif',
            ],
            'post_upload' => [
                SaveEntityItemRelationHandler::CODENAME,
            ],

            'storage' => [
                'name' => 'LocalPublic',
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

            'storage' => [
                'name' => 'LocalPublic',
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

            'storage' => [
                'name' => 'LocalPublic',
                'path' => 'content-attachments'
            ],
        ]
    ],
];
