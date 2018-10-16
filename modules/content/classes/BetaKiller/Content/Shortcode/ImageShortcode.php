<?php
namespace BetaKiller\Content\Shortcode;

use BetaKiller\Content\Shortcode\Attribute\BooleanAttribute;
use BetaKiller\Content\Shortcode\Attribute\NumberAttribute;
use BetaKiller\Content\Shortcode\Attribute\SwitchAttribute;
use BetaKiller\Content\Shortcode\Editor\EditorListingItem;
use BetaKiller\Helper\AssetsHelper;
use BetaKiller\Model\ContentImageInterface;
use BetaKiller\Model\EntityModelInterface;
use BetaKiller\Repository\ContentImageRepository;

class ImageShortcode extends AbstractContentElementShortcode
{
    private const LAYOUT_DEFAULT = 'default';
    private const LAYOUT_CAPTION = 'caption';

    private const ATTR_ZOOMABLE = 'zoomable';
    private const ATTR_ALIGN    = 'align';

    private const ATTR_ALIGN_LEFT    = 'left';
    private const ATTR_ALIGN_RIGHT   = 'right';
    private const ATTR_ALIGN_CENTER  = 'center';
    private const ATTR_ALIGN_JUSTIFY = 'justify';

    private const ATTR_ALIGN_VALUES = [
        self::ATTR_ALIGN_JUSTIFY,
        self::ATTR_ALIGN_CENTER,
        self::ATTR_ALIGN_LEFT,
        self::ATTR_ALIGN_RIGHT,
    ];

    private const ATTR_WIDTH = 'width';

    /**
     * @var \BetaKiller\Repository\ContentImageRepository
     */
    private $imageRepository;

    /**
     * @var \BetaKiller\Helper\AssetsHelper
     */
    private $assetsHelper;

    /**
     * ImageShortcode constructor.
     *
     * @param \BetaKiller\Content\Shortcode\ShortcodeEntityInterface $entity
     * @param \BetaKiller\Repository\ContentImageRepository          $repository
     * @param \BetaKiller\Helper\AssetsHelper                        $helper
     */
    public function __construct(
        ShortcodeEntityInterface $entity,
        ContentImageRepository $repository,
        AssetsHelper $helper
    ) {
        $this->imageRepository = $repository;
        $this->assetsHelper    = $helper;

        parent::__construct($entity);
    }

    /**
     * @return \BetaKiller\Content\Shortcode\Attribute\ShortcodeAttributeInterface[]
     */
    protected function getContentElementShortcodeDefinitions(): array
    {
        return [
            (new SwitchAttribute(self::ATTR_ALIGN, self::ATTR_ALIGN_VALUES))
                ->optional(self::ATTR_ALIGN_JUSTIFY),

            (new BooleanAttribute(self::ATTR_ZOOMABLE))
                ->optionalFalse(),

            (new NumberAttribute(self::ATTR_WIDTH))
                ->optional(),
        ];
    }

    /**
     * @return string[]
     */
    protected function getAvailableLayouts(): array
    {
        return [
            self::LAYOUT_DEFAULT,
            self::LAYOUT_CAPTION,
        ];
    }

    /**
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function enableZoomable(): void
    {
        $this->setAttribute(self::ATTR_ZOOMABLE, BooleanAttribute::VALUE_TRUE);
    }

    /**
     * @return bool
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function isZoomable(): bool
    {
        return $this->getAttribute(self::ATTR_ZOOMABLE) === BooleanAttribute::VALUE_TRUE;
    }

    /**
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function useCaptionLayout(): void
    {
        $this->setLayout(self::LAYOUT_CAPTION);
    }

    /**
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function setAlignLeft(): void
    {
        $this->setAlign(self::ATTR_ALIGN_LEFT);
    }

    /**
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function setAlignRight(): void
    {
        $this->setAlign(self::ATTR_ALIGN_RIGHT);
    }

    /**
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function setAlignCenter(): void
    {
        $this->setAlign(self::ATTR_ALIGN_CENTER);
    }

    /**
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function setAlignJustify(): void
    {
        $this->setAlign(self::ATTR_ALIGN_JUSTIFY);
    }

    /**
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function setAlignNone(): void
    {
        $this->setAlign(null);
    }

    /**
     * @param string|null $value
     *
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    private function setAlign(?string $value): void
    {
        $this->setAttribute(self::ATTR_ALIGN, $value);
    }

    /**
     * @param int $value
     *
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function setWidth(int $value): void
    {
        $this->setAttribute(self::ATTR_WIDTH, $value);
    }

    /**
     * @return string
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function getWysiwygPluginPreviewSrc(): string
    {
        $id    = (int)$this->getID();
        $model = $this->imageRepository->findById($id);

        return $this->assetsHelper->getOriginalUrl($model);
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
        $model = $this->getCurrentImageModel();

        $align = $this->getAttribute(self::ATTR_ALIGN) ?? null;
        $width = $this->getAttribute(self::ATTR_WIDTH);

        $attributes = [
            'id'    => 'content-image-'.$model->getID(),
            'title' => $model->getTitle(),
            'alt'   => $model->getAlt(),
            'width' => $width,
        ];

        $alignClass = $align ? 'align'.$align : 'alignnone';

        return [
            'layout'   => $this->getLayout(),
            'zoomable' => $this->isZoomable(),

            'caption'    => $model->getTitle(),
            'alignClass' => $alignClass,

            'image' => $this->assetsHelper->getAttributesForImgTag($model, $model::SIZE_ORIGINAL, $attributes),
            'href'  => $this->assetsHelper->getOriginalUrl($model),
        ];
    }

    /**
     * @return \BetaKiller\Model\ContentImageInterface
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    private function getCurrentImageModel(): ContentImageInterface
    {
        $imageID = (int)$this->getID();

        if (!$imageID) {
            throw new ShortcodeException('No image ID provided');
        }

        return $this->imageRepository->findById($imageID);
    }

    /**
     * @param \BetaKiller\Model\EntityModelInterface|null $relatedEntity
     * @param int|null                                    $itemID
     *
     * @return \BetaKiller\Content\Shortcode\Editor\EditorListingItem[]
     */
    public function getEditorListingItems(?EntityModelInterface $relatedEntity, ?int $itemID): array
    {
        $images = $this->imageRepository->getEditorListing($relatedEntity, $itemID);

        $data = [];

        foreach ($images as $image) {
            $data[] = new EditorListingItem(
                $image->getID(),
                $image->getTitle() ?: $image->getOriginalName(),
                $image->isValid(),
                $this->assetsHelper->getPreviewUrl($image),
                $image->getMime()
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
        $model = $this->getCurrentImageModel();

        return [
            'alt'   => $model->getAlt(),
            'title' => $model->getTitle(),
            'url'   => $this->assetsHelper->getOriginalUrl($model),
        ];
    }

    /**
     * Update model data (based on "id" attribute value)
     *
     * @param array $data
     *
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function updateEditorItemData(array $data): void
    {
        $model = $this->getCurrentImageModel();

        if (isset($data['alt'])) {
            // Sanitize data
            $model->setAlt(\trim(\HTML::entities($data['alt'])));
        }

        if (isset($data['title'])) {
            // Sanitize data
            $model->setTitle(\trim(\HTML::entities($data['title'])));
        }

        $this->imageRepository->save($model);
    }

    /**
     * Return url for uploading new items or null if items can not be uploaded and must be added via regular edit form
     *
     * @return null|string
     */
    public function getEditorItemUploadUrl(): ?string
    {
        // Create empty model for detecting assets provider
        $model = $this->imageRepository->create();

        return $this->assetsHelper->getUploadUrl($model);
    }

    /**
     * Return array of allowed mime-types
     *
     * @return string[]
     * @throws \BetaKiller\Assets\Exception\AssetsException
     */
    public function getEditorItemAllowedMimeTypes(): array
    {
        // Create empty model for detecting assets provider
        $model = $this->imageRepository->create();

        return $this->assetsHelper->getAllowedMimeTypes($model);
    }
}
