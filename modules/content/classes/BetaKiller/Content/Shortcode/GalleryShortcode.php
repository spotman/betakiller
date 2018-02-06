<?php
namespace BetaKiller\Content\Shortcode;

use BetaKiller\Content\Shortcode\Attribute\NumberAttribute;
use BetaKiller\Content\Shortcode\Editor\EditorListingItem;
use BetaKiller\Model\ContentGalleryInterface;
use BetaKiller\Model\EntityModelInterface;

class GalleryShortcode extends AbstractContentElementShortcode
{
    public const LAYOUT_TABLE   = 'default';
    public const LAYOUT_MASONRY = 'masonry';
    public const LAYOUT_SLIDER  = 'slider';

    public const ATTR_COLUMNS = 'columns';

    /**
     * @var \BetaKiller\Helper\AssetsHelper
     * @Inject
     */
    private $assetsHelper;

    /**
     * @Inject
     * @var \BetaKiller\Repository\ContentGalleryRepository
     */
    private $galleryRepository;

    /**
     * @return \BetaKiller\Content\Shortcode\Attribute\ShortcodeAttributeInterface[]
     */
    protected function getContentElementShortcodeDefinitions(): array
    {
        return [
            new NumberAttribute('columns', true),
        ];
    }

    /**
     * @return string[]
     */
    protected function getAvailableLayouts(): array
    {
        return [
            self::LAYOUT_TABLE,
            self::LAYOUT_MASONRY,
            self::LAYOUT_SLIDER,
        ];
    }

    /**
     * @param int|null $columns
     *
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function useTableLayout(?int $columns = null): void
    {
        $this->setLayout(self::LAYOUT_TABLE);

        if ($columns) {
            $this->setColumns($columns);
        }
    }

    /**
     * @param int|null $columns
     *
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function useMasonryLayout(?int $columns = null): void
    {
        $this->setLayout(self::LAYOUT_MASONRY);

        if ($columns) {
            $this->setColumns($columns);
        }
    }

    /**
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function useSliderLayout(): void
    {
        $this->setLayout(self::LAYOUT_SLIDER);
    }

    /**
     * @param int $value
     *
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function setColumns(int $value): void
    {
        $this->setAttribute(self::ATTR_COLUMNS, $value);
    }

    /**
     * @return array
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function getWidgetData(): array
    {
        $id = (int)$this->getID();

        $gallery = $this->galleryRepository->findById($id);
        $images  = $this->getImages($gallery, true);

        $layout  = $this->getLayout();
        $columns = (int)($this->getAttribute('columns') ?? 3);

        $imagesData = [];

        foreach ($images as $model) {
            $imagesData[] = [
                'href' => $this->assetsHelper->getOriginalUrl($model),
                'attributes' => $this->assetsHelper->getAttributesForImgTag($model, $model::SIZE_PREVIEW),
            ];
        }

        return [
            'id'      => $id,
            'images'  => $imagesData,
            'layout'  => $layout,
            'columns' => $columns,
        ];
    }

    /**
     * @param \BetaKiller\Model\ContentGalleryInterface $gallery
     *
     * @param bool|null                                 $throwIfAbsent
     *
     * @return \BetaKiller\Model\ContentImageInterface[]
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    private function getImages(ContentGalleryInterface $gallery, bool $throwIfAbsent = null): array
    {
        $throwIfAbsent = $throwIfAbsent ?? true;
        $images        = $gallery->getImages();

        if (!$images && $throwIfAbsent) {
            throw new ShortcodeException('Gallery [:id] has no images', [
                ':id' => $gallery->getID(),
            ]);
        }

        return $images;
    }

    /**
     * @return string
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function getWysiwygPluginPreviewSrc(): string
    {
        $id      = (int)$this->getID();
        $gallery = $this->galleryRepository->findById($id);

        return $this->getPreviewUrl($gallery);
    }

    /**
     * @param \BetaKiller\Model\ContentGalleryInterface $gallery
     *
     * @return string
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    private function getPreviewUrl(ContentGalleryInterface $gallery): string
    {
        $images     = $this->getImages($gallery, true);
        $firstImage = array_pop($images);

        return $this->assetsHelper->getOriginalUrl($firstImage);
    }

    /**
     * @param \BetaKiller\Model\EntityModelInterface|null $relatedEntity
     * @param int|null                                    $itemID
     *
     * @return \BetaKiller\Content\Shortcode\Editor\EditorListingItem[]
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function getEditorListingItems(?EntityModelInterface $relatedEntity, ?int $itemID): array
    {
        $galleries = $this->galleryRepository->getEditorListing($relatedEntity, $itemID);

        $data = [];

        foreach ($galleries as $gallery) {
            $data[] = new EditorListingItem(
                $gallery->getID(),
                $this->getPreviewUrl($gallery),
                $gallery->isValid()
            );
        }

        return $data;
    }
}
