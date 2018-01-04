<?php

return [
    'shortcodes' => [
        \BetaKiller\Content\Shortcode\ImageShortcode::codename() => [
            'tag_name' => 'image',
            'is_editable' => true,
        ],

        \BetaKiller\Content\Shortcode\GalleryShortcode::codename() => [
            'tag_name' => 'gallery',
            'is_editable' => true,
        ],

        \BetaKiller\Content\Shortcode\YoutubeShortcode::codename() => [
            'tag_name' => 'youtube',
            'is_editable' => true,
        ],

        \BetaKiller\Content\Shortcode\AttachmentShortcode::codename() => [
            'tag_name' => 'attachment',
            'is_editable' => true,
        ],
    ]
];
