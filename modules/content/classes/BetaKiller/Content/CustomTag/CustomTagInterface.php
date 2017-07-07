<?php
namespace BetaKiller\Content\CustomTag;

use BetaKiller\IFace\Url\NonPersistentUrlParameterInterface;

interface CustomTagInterface extends NonPersistentUrlParameterInterface
{
    const CLASS_SUFFIX      = 'CustomTag';
    const URL_CONTAINER_KEY = 'CustomTag';

    /**
     * Returns HTML tag name
     *
     * @return string
     */
    public function getTagName(): string;

    public function getWysiwygPluginPreviewSrc(array $attributes): string;
}
