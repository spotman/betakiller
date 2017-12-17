<?php
namespace BetaKiller\Content\Shortcode;

class StaticShortcode extends AbstractShortcode
{
    /**
     * Returns true if current tag is editable in WYSIWYG editor
     *
     * @return bool
     */
    public function isEditable(): bool
    {
        return false;
    }

    /**
     * Returns true if current tag may have text content between open and closing markers
     *
     * @return bool
     */
    public function mayHaveContent(): bool
    {
        return false;
    }

    public function getWysiwygPluginPreviewSrc(): string
    {
        return '';
    }

    /**
     * @return array
     */
    public function getWidgetData(): array
    {
        return [];
    }
}
