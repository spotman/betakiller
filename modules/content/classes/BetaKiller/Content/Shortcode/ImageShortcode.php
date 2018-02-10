<?php
namespace BetaKiller\Content\Shortcode;

use BetaKiller\Content\Shortcode\Attribute\BooleanAttribute;
use BetaKiller\Content\Shortcode\Attribute\DiscreteValuesAttribute;
use BetaKiller\Content\Shortcode\Attribute\NumberAttribute;
use BetaKiller\Content\Shortcode\Attribute\TextAttribute;
use BetaKiller\Content\Shortcode\Editor\EditorListingItem;
use BetaKiller\Helper\AssetsHelper;
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
        self::ATTR_ALIGN_LEFT,
        self::ATTR_ALIGN_RIGHT,
        self::ATTR_ALIGN_CENTER,
        self::ATTR_ALIGN_JUSTIFY,
    ];

    private const ATTR_ALT   = 'alt';
    private const ATTR_TITLE = 'title';
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
            new BooleanAttribute(self::ATTR_ZOOMABLE, true),
            new TextAttribute(self::ATTR_ALT, true),
            new TextAttribute(self::ATTR_TITLE, true),
            new DiscreteValuesAttribute(self::ATTR_ALIGN, self::ATTR_ALIGN_VALUES, true),
//            new ClassAttribute(true),
            new NumberAttribute(self::ATTR_WIDTH, true),
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
        $this->setAttribute(self::ATTR_ZOOMABLE, BooleanAttribute::TRUE);
    }

    /**
     * @return bool
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function isZoomable(): bool
    {
        return $this->getAttribute(self::ATTR_ZOOMABLE) === BooleanAttribute::TRUE;
    }

    /**
     * @param string $title
     *
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function useCaptionLayout(string $title): void
    {
        $this->setLayout(self::LAYOUT_CAPTION);
        $this->setAttribute('title', $title);
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
     * @throws \BetaKiller\Assets\AssetsException
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function getWidgetData(): array
    {
        $imageID = (int)$this->getID();

        if (!$imageID) {
            throw new ShortcodeException('No image ID provided');
        }

        $model = $this->imageRepository->findById($imageID);

        $title = $this->getAttribute(self::ATTR_TITLE);
        $align = $this->getAttribute(self::ATTR_ALIGN) ?? null;
        $alt   = $this->getAttribute(self::ATTR_ALT);
        $width = $this->getAttribute(self::ATTR_WIDTH);

        $attributes = [
            'id'    => 'content-image-'.$model->getID(),
            'title' => $title ?: $model->getTitle(),
            'alt'   => $alt ?: $model->getAlt(),
            'width' => $width,
        ];

        $alignClass = $align ? 'align'.$align : 'alignnone';

        return [
            'layout'   => $this->getLayout(),
            'zoomable' => $this->isZoomable(),

            'caption'    => $title,
            'alignClass' => $alignClass,

            'image' => $this->assetsHelper->getAttributesForImgTag($model, $model::SIZE_ORIGINAL, $attributes),
            'href'  => $this->assetsHelper->getOriginalUrl($model),
        ];
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
                $this->assetsHelper->getPreviewUrl($image),
                $image->isValid()
            );
        }

        return $data;
    }
}
