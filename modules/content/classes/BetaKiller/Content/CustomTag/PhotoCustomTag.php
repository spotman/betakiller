<?php
namespace BetaKiller\Content\CustomTag;

use BetaKiller\Helper\AssetsHelper;
use BetaKiller\Repository\ContentImageRepository;

class PhotoCustomTag extends AbstractCustomTag
{
    const TAG_NAME                   = 'photo';
    const ATTRIBUTE_ZOOMABLE_NAME    = 'zoomable';
    const ATTRIBUTE_ZOOMABLE_ENABLED = 'true';

    /**
     * @var \BetaKiller\Repository\ContentImageRepository
     */
    private $imageRepository;

    /**
     * @var \BetaKiller\Helper\AssetsHelper
     */
    private $assetsHelper;

    /**
     * PhotoCustomTag constructor.
     *
     * @param \BetaKiller\Repository\ContentImageRepository $repository
     * @param \BetaKiller\Helper\AssetsHelper               $helper
     */
    public function __construct(ContentImageRepository $repository, AssetsHelper $helper)
    {
        $this->imageRepository = $repository;
        $this->assetsHelper = $helper;
    }

    /**
     * Returns HTML tag name
     *
     * @return string
     */
    public function getTagName(): string
    {
        return self::TAG_NAME;
    }

    public function getWysiwygPluginPreviewSrc(array $attributes): string
    {
        $id = (int)$attributes['id'];
        $model = $this->imageRepository->findById($id);

        return $this->assetsHelper->getOriginalUrl($model);
    }
}
