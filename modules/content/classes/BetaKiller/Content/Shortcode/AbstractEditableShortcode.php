<?php
namespace BetaKiller\Content\Shortcode;

abstract class AbstractEditableShortcode extends AbstractShortcode
{
    /**
     * Returns true if current tag is editable in WYSIWYG editor
     *
     * @return bool
     */
    public function isEditable(): bool
    {
        return true;
    }
}
