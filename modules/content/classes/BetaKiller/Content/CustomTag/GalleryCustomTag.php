<?php
namespace BetaKiller\Content\CustomTag;

class GalleryCustomTag extends AbstractCustomTag
{
    const TAG_NAME = 'gallery';

    /**
     * Returns HTML tag name
     *
     * @return string
     */
    public function getTagName(): string
    {
        return self::TAG_NAME;
    }

    public function getWysiwygPluginPreviewSrc(array $attributes): string
    {
        // TODO Show slider or gallery image (depends on attributes)
        return '/assets/static/images/gallery-wysiwyg.png';
    }
}
