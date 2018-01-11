<?php
use BetaKiller\Content\Shortcode\ShortcodeEntityInterface;

return [
    'shortcodes' => [
        \BetaKiller\Content\Shortcode\ImageShortcode::codename() => [
            'type'  => ShortcodeEntityInterface::TYPE_CONTENT_ELEMENT,
            'tag_name' => 'image',
        ],

        \BetaKiller\Content\Shortcode\GalleryShortcode::codename() => [
            'type'  => ShortcodeEntityInterface::TYPE_CONTENT_ELEMENT,
            'tag_name' => 'gallery',
        ],

        \BetaKiller\Content\Shortcode\YoutubeShortcode::codename() => [
            'type'  => ShortcodeEntityInterface::TYPE_CONTENT_ELEMENT,
            'tag_name' => 'youtube',
        ],

        \BetaKiller\Content\Shortcode\AttachmentShortcode::codename() => [
            'type'  => ShortcodeEntityInterface::TYPE_CONTENT_ELEMENT,
            'tag_name' => 'attachment',
        ],
    ]
];
