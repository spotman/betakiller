<?php
namespace BetaKiller\Content\Shortcode;

class DynamicShortcode extends AbstractShortcode
{
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
