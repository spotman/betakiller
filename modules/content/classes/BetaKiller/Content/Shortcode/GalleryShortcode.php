<?php
namespace BetaKiller\Content\Shortcode;

class GalleryShortcode extends AbstractEditableShortcode
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

    public function __construct()
    {
        parent::__construct('gallery');
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

        $layout = $this->getAttribute('layout') ?? $allowedLayouts[0];

        if (!\in_array($layout, $allowedLayouts, true)) {
            throw new ShortcodeException('Unknown gallery layout :value', [':value' => $layout]);
        }

        $columns = (int)($this->getAttribute('columns') ?? 3);

        $images = [];

        foreach ($imageIDs as $id) {
            /** @var \BetaKiller\Model\ContentImage $model */
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
        // TODO Show slider or gallery image (depends on attributes)
        return '/assets/static/images/gallery-wysiwyg.png';
    }
}
