<?php
namespace BetaKiller\Content\Shortcode;

use BetaKiller\Content\Shortcode\Attribute\CommaSeparatedIDsAttribute;
use BetaKiller\Content\Shortcode\Attribute\NumberAttribute;

class GalleryShortcode extends AbstractContentElementShortcode
{
    private const LAYOUT_MASONRY = 'masonry';
    private const LAYOUT_SLIDER  = 'slider';

    /**
     * @var \BetaKiller\Helper\AssetsHelper
     * @Inject
     */
    private $assetsHelper;

    /**
     * @Inject
     * @var \BetaKiller\Repository\ContentImageRepository
     */
    private $repository;

    /**
     * @Inject
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @return \BetaKiller\Content\Shortcode\Attribute\ShortcodeAttributeInterface[]
     */
    protected function getContentElementShortcodeDefinitions(): array
    {
        return [
            new CommaSeparatedIDsAttribute('ids'),
            new NumberAttribute('column', true),
        ];
    }

    /**
     * @return string[]
     */
    protected function getAvailableLayouts(): array
    {
        return [
            self::LAYOUT_MASONRY,
            self::LAYOUT_SLIDER,
        ];
    }

    /**
     * @return array
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function getWidgetData(): array
    {
        $imageIDs = explode(',', $this->getAttribute('ids'));

        if (!$imageIDs) {
            throw new ShortcodeException('No image IDs provided');
        }

        $layout  = $this->getLayout(self::LAYOUT_MASONRY);
        $columns = (int)($this->getAttribute('columns') ?? 3);

        $images = [];

        foreach ($imageIDs as $id) {
            $model = $this->repository->findById($id);

            $images[] = $this->assetsHelper->getAttributesForImgTag($model, $model::SIZE_PREVIEW);
        }

        if (!$images) {
            $this->logger->warning('Gallery has no images for ids [:ids]', [
                ':ids' => implode(', ', $imageIDs),
            ]);
        }

        return [
            'images'  => $images,
            'layout'  => $layout,
            'columns' => $columns,
        ];
    }

    /**
     * @return string
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function getWysiwygPluginPreviewSrc(): string
    {
        $imageIDs = explode(',', $this->getAttribute('ids'));

        if (!$imageIDs) {
            throw new ShortcodeException('No image IDs provided');
        }

        $firstID = array_pop($imageIDs);

        $model = $this->repository->findById($firstID);

        return $this->assetsHelper->getOriginalUrl($model);
    }
}
