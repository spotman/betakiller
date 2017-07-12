<?php
namespace BetaKiller\Content\CustomTag;

interface CustomTagInterface
{
    const CLASS_SUFFIX      = 'CustomTag';
    const URL_CONTAINER_KEY = 'CustomTag';

    /**
     * @return string
     */
    public static function getCodename(): string;

    /**
     * Returns HTML tag name
     *
     * @return string
     */
    public function getTagName(): string;

    public function getWysiwygPluginPreviewSrc(array $attributes): string;
}
