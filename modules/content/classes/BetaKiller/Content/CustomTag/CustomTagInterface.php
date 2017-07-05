<?php
namespace BetaKiller\Content\CustomTag;

interface CustomTagInterface
{
    public function getWysiwygPluginPreviewSrc(array $attributes): string;
}
