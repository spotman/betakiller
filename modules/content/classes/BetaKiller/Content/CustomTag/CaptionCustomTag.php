<?php
namespace BetaKiller\Content\CustomTag;

class CaptionCustomTag extends PhotoCustomTag
{
    const TAG_NAME = 'caption';

    /**
     * Returns HTML tag name
     *
     * @return string
     */
    public function getTagName(): string
    {
        return self::TAG_NAME;
    }
}
