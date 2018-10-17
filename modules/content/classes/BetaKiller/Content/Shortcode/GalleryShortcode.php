<?php
namespace BetaKiller\Content\Shortcode;

use BetaKiller\Content\Shortcode\Attribute\SwitchAttribute;
use BetaKiller\Content\Shortcode\Editor\EditorListingItem;
use BetaKiller\Model\ContentGalleryInterface;
use BetaKiller\Model\EntityModelInterface;

class GalleryShortcode extends AbstractContentElementShortcode
{
    public const LAYOUT_TABLE   = 'default';
    public const LAYOUT_MASONRY = 'masonry';
    public const LAYOUT_SLIDER  = 'slider';

    public const ATTR_COLUMNS       = 'columns';
    public const ATTR_COLUMNS_ONE   = '1';
    public const ATTR_COLUMNS_TWO   = '2';
    public const ATTR_COLUMNS_THREE = '3';
    public const ATTR_COLUMNS_FOUR  = '4';
    public const ATTR_COLUMNS_FIVE  = '5';
    public const ATTR_COLUMNS_SIX   = '6';
    public const ATTR_COLUMNS_SEVEN = '7';

    public const ATTR_COLUMNS_VALUES = [
        self::ATTR_COLUMNS_ONE,
        self::ATTR_COLUMNS_TWO,
        self::ATTR_COLUMNS_THREE,
        self::ATTR_COLUMNS_FOUR,
        self::ATTR_COLUMNS_FIVE,
        self::ATTR_COLUMNS_SIX,
        self::ATTR_COLUMNS_SEVEN,
    ];

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
            (new SwitchAttribute('columns', self::ATTR_COLUMNS_VALUES))
                ->optional(self::ATTR_COLUMNS_THREE),
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
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Assets\AssetsStorageException
     * @throws \BetaKiller\Assets\Exception\AssetsException
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function getWidgetData(): array
    {
        $gallery = $this->getGallery();
        $images  = $this->getImages($gallery, true);

        $layout  = $this->getLayout();
        $columns = (int)($this->getAttribute('columns') ?? 3);

        $imagesData = [];

        foreach ($images as $model) {
            $imagesData[] = [
                'href'       => $this->assetsHelper->getOriginalUrl($model),
                'attributes' => $this->assetsHelper->getAttributesForImgTag($model, $model::SIZE_PREVIEW),
            ];
        }

        return [
            'id'      => $gallery->getID(),
            'images'  => $imagesData,
            'layout'  => $layout,
            'columns' => $columns,
        ];
    }

    /**
     * @return \BetaKiller\Model\ContentGalleryInterface
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    private function getGallery(): ContentGalleryInterface
    {
        $id = (int)$this->getID();

        return $this->galleryRepository->findById($id);
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
        $gallery = $this->getGallery();

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
                $gallery->getID(),
                $gallery->isValid(),
                $this->getPreviewUrl($gallery)
            );
        }

        return $data;
    }

    /**
     * Returns item data (based on "id" attribute value)
     *
     * @return array
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function getEditorItemData(): array
    {
        $gallery = $this->getGallery();
        $images = $this->getImages($gallery, false);

        $imagesIDs = [];

        foreach ($images as $image) {
            $imagesIDs[] = (int)$image->getID(); // EditorListing items IDs are always integers
        }
        // No data for editing
        return [
            'id' => $gallery->getID(),
            'images' => $imagesIDs,
        ];
    }

    /**
     * Update model data (based on "id" attribute value)
     *
     * @param array $data
     */
    public function updateEditorItemData(array $data): void
    {
        // Nothing to do coz there is no editable data
    }

    /**
     * Return url for uploading new items or null if items can not be uploaded and must be added via regular edit form
     *
     * @return null|string
     */
    public function getEditorItemUploadUrl(): ?string
    {
        // TODO Get upload URL for images repository
        return null;
    }

    /**
     * Return array of allowed mime-types
     *
     * @return string[]
     */
    public function getEditorItemAllowedMimeTypes(): array
    {
        // TODO Get mime-types for images repository
        return [];
    }
}
