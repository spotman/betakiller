<?php
namespace BetaKiller\Content\Shortcode;

class GalleryShortcode extends AbstractContentElementShortcode
{
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
     * Returns true if current tag may have text content between open and closing markers
     *
     * @return bool
     */
    public function mayHaveContent(): bool
    {
        return false;
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

        $allowedLayouts = [
            'masonry',
            'slider',
        ];

        $layout = $this->getLayout() ?? $allowedLayouts[0];

        if (!\in_array($layout, $allowedLayouts, true)) {
            throw new ShortcodeException('Unknown gallery layout :value', [':value' => $layout]);
        }

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

    public function getWysiwygPluginPreviewSrc(): string
    {
        $imageIDs = explode(',', $this->getAttribute('ids'));

        if (!$imageIDs) {
            throw new ShortcodeException('No image IDs provided');
        }

        $firstID = array_pop($imageIDs);

        $model = $this->repository->findById($firstID);

        // TODO Show slider or gallery image (depends on attributes)
        return $this->assetsHelper->getOriginalUrl($model);
    }
}
