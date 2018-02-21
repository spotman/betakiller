<?php
namespace BetaKiller\Content\Shortcode\Attribute;


class StringAttribute extends AbstractShortcodeAttribute
{
    /**
     * Returns attribute type
     *
     * @return string
     */
    public function getType(): string
    {
        return self::TYPE_STRING;
    }

    public function isValueAvailable(string $value): bool
    {
        // Any text is available here
        return true;
    }
}
