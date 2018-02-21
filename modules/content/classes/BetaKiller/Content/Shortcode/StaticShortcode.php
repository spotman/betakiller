<?php
namespace BetaKiller\Content\Shortcode;

class StaticShortcode extends AbstractShortcode
{
    /**
     * @return array
     */
    public function getAttributesDefinitions(): array
    {
        // No attributes available in static shortcodes
        return [];
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
