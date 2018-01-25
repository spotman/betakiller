<?php
namespace BetaKiller\Content\Shortcode;

use BetaKiller\Content\Shortcode\Attribute\BooleanAttribute;
use BetaKiller\Content\Shortcode\Attribute\ClassAttribute;
use BetaKiller\Content\Shortcode\Attribute\NumberAttribute;
use BetaKiller\Content\Shortcode\Attribute\StyleAttribute;
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
            new TextAttribute('alt', true),
            new TextAttribute('title', true),
            new TextAttribute('align', true),
            new ClassAttribute(true),
            new StyleAttribute(),
            new NumberAttribute('width', true),
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
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function getWidgetData(): array
    {
        $imageID = (int)$this->getID();

        if (!$imageID) {
            throw new ShortcodeException('No image ID provided');
        }

        $model = $this->imageRepository->findById($imageID);

        $title  = $this->getAttribute('title');
        $align  = $this->getAttribute('align') ?? 'alignnone';
        $alt    = $this->getAttribute('alt');
        $class  = $this->getAttribute('class');
        $style  = $this->getAttribute('style');
        $width  = (int)$this->getAttribute('width');

        $classes = array_unique(array_filter(explode(' ', $class)));

        if (strpos($class, 'align') === false) {
            $classes[] = $align;
        }

        $attributes = [
            'id'     => 'content-image-'.$model->getID(),
            'title'  => $title ?: $model->getTitle(),
            'alt'    => $alt ?: $model->getAlt(),
            'class'  => implode(' ', $classes),
            'style'  => $style,
            'width'  => $width,
        ];

        return [
            'layout'   => $this->getAttribute(ContentElementShortcodeInterface::ATTR_LAYOUT) ?? self::LAYOUT_DEFAULT,
            'zoomable' => $this->isZoomable(),

            'caption' => $title,
            'align'   => $align,
            'class'   => $class,

            'image' => $this->assetsHelper->getAttributesForImgTag($model, $model::SIZE_ORIGINAL, $attributes),
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
