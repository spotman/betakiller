<?php
namespace BetaKiller\Content\Shortcode\Attribute;


class TextAttribute extends AbstractShortcodeAttribute
{
    public function isValueAvailable(string $value): bool
    {
        // Any text is available here
        return true;
    }
}
