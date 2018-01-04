<?php
namespace BetaKiller\Content\Shortcode;

use BetaKiller\Helper\AssetsHelper;
use BetaKiller\Repository\ContentImageRepository;

class ImageShortcode extends AbstractContentElementShortcode
{
    public const ATTR_LAYOUT_CAPTION = 'caption';

    private const ATTR_ZOOMABLE_NAME    = 'zoomable';
    private const ATTR_ZOOMABLE_ENABLED = 'true';

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
     * @param string                                        $tagName
     * @param \BetaKiller\Repository\ContentImageRepository $repository
     * @param \BetaKiller\Helper\AssetsHelper               $helper
     */
    public function __construct(string $tagName, ContentImageRepository $repository, AssetsHelper $helper)
    {
        $this->imageRepository = $repository;
        $this->assetsHelper    = $helper;

        parent::__construct($tagName);
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

    public function enableZoomable(): void
    {
        $this->setAttribute(self::ATTR_ZOOMABLE_NAME, self::ATTR_ZOOMABLE_ENABLED);
    }

    public function isZoomable(): bool
    {
        return ($this->getAttribute(self::ATTR_ZOOMABLE_NAME) === self::ATTR_ZOOMABLE_ENABLED);
    }

    public function useCaptionLayout(string $title): void
    {
        $this->setLayout(self::ATTR_LAYOUT_CAPTION);
        $this->setAttribute('title', $title);
    }

    public function getWysiwygPluginPreviewSrc(): string
    {
        $id    = (int)$this->getAttribute('id');
        $model = $this->imageRepository->findById($id);

        return $this->assetsHelper->getOriginalUrl($model);
    }

    /**
     * @return array
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function getWidgetData(): array
    {
        $imageID = (int)$this->getAttribute('id');

        if (!$imageID) {
            throw new ShortcodeException('No image ID provided');
        }

        $model = $this->imageRepository->findById($imageID);

        $layouts = [
            'default',
            'caption',
        ];

        $title = $this->getAttribute('title');
        $align = $this->getAttribute('align') ?? 'alignnone';
        $alt   = $this->getAttribute('alt');
        $class = $this->getAttribute('class');
        $width = (int)$this->getAttribute('width');

        if (strpos($class, 'align') === false) {
            $classes[] = $align;
        }

        $classes = array_filter(explode(' ', $class));

        $layout = $this->getAttribute(self::ATTR_LAYOUT) ?? $layouts[0];

        if (!\in_array($layout, $layouts, true)) {
            throw new ShortcodeException('Incorrect image layout :value', [':value' => $layout]);
        }

        $attributes = [
            'id'    => 'content-image-'.$model->getID(),
            'title' => $title ?: $model->getTitle(),
            'alt'   => $alt ?: $model->getAlt(),
            'class' => implode(' ', array_unique($classes)),
        ];

        if ($width) {
            $attributes['style'] = 'width: '.$width.'px';
        }

        return [
            'layout'   => $layout,
            'zoomable' => $this->isZoomable(),

            'caption' => $title,
            'align'   => $align,
            'class'   => $class,
            'width'   => $width,

            'image' => $this->assetsHelper->getAttributesForImgTag($model, $model::SIZE_ORIGINAL, $attributes),
        ];
    }
}
