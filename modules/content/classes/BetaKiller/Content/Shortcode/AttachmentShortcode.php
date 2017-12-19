<?php
namespace BetaKiller\Content\Shortcode;

use BetaKiller\Helper\AssetsHelper;
use BetaKiller\Repository\ContentAttachmentRepository;
use BetaKiller\Repository\ContentImageRepository;

class AttachmentShortcode extends AbstractContentElementShortcode
{
    private const ATTR_LAYOUT_TEXT  = 'text';
    private const ATTR_LAYOUT_IMAGE = 'image';
    private const ATTR_IMAGE_ID     = 'image-id';
    private const ATTR_LABEL        = 'label';

    /**
     * @var \BetaKiller\Repository\ContentAttachmentRepository
     */
    private $attachmentRepository;

    /**
     * @var \BetaKiller\Repository\ContentImageRepository
     */
    private $imageRepository;

    /**
     * @var \BetaKiller\Helper\AssetsHelper
     */
    private $assetsHelper;

    /**
     * AttachmentShortcode constructor.
     *
     * @param \BetaKiller\Repository\ContentAttachmentRepository $repository
     * @param \BetaKiller\Repository\ContentImageRepository      $imageRepository
     * @param \BetaKiller\Helper\AssetsHelper                    $helper
     */
    public function __construct(
        ContentAttachmentRepository $repository,
        ContentImageRepository $imageRepository,
        AssetsHelper $helper
    ) {
        $this->attachmentRepository = $repository;
        $this->imageRepository      = $imageRepository;
        $this->assetsHelper         = $helper;

        parent::__construct('attachment');
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

    public function getWysiwygPluginPreviewSrc(): string
    {
//        $id = (int)$attributes['id'];
//        $model = $this->attachmentRepository->findById($id);

        // TODO Show button or link (depends on attributes)
        return '/assets/static/images/download-button.png';
    }

    /**
     * @return array
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function getWidgetData(): array
    {
        $attachID = (int)$this->getAttribute('id');

        if (!$attachID) {
            throw new ShortcodeException('No attachment ID provided');
        }

        $model = $this->attachmentRepository->findById($attachID);

        $title = \HTML::chars($this->getAttribute('title'));
        $class = $this->getAttribute('class');

        $i18nParams = [
            ':name' => $model->getOriginalName(),
        ];

        $imageUrl = ($this->getLayout() === self::ATTR_LAYOUT_IMAGE)
            ? $this->getImageUrl()
            : null;

        return [
            'image'  => $imageUrl,
            'label'  => $this->getAttribute(self::ATTR_LABEL),
            'url'    => $this->assetsHelper->getDownloadUrl($model),
            'title'  => $title ?: __('custom_tag.attachment.title', $i18nParams),
            'alt'    => __('custom_tag.attachment.alt', $i18nParams),
            'class'  => $class,
            'layout' => $this->getLayout(),
        ];
    }

    private function getImageUrl(): string
    {
        $id = $this->getAttribute(self::ATTR_IMAGE_ID);

        if (!$id) {
            throw new ShortcodeException('Missing image_id attribute');
        }

        $image = $this->imageRepository->findById($id);

        return $this->assetsHelper->getOriginalUrl($image);
    }

    public function useImageLayout(int $imageID): void
    {
        $this->setLayout(self::ATTR_LAYOUT_IMAGE);
        $this->setImageID($imageID);
    }

    public function useTextLayout(string $label): void
    {
        $this->setLayout(self::ATTR_LAYOUT_TEXT);
        $this->setAttribute(self::ATTR_LABEL, $label);
        $this->setAttribute(self::ATTR_IMAGE_ID, null);
    }

    protected function setImageID(int $value): void
    {
        $this->setAttribute(self::ATTR_IMAGE_ID, $value);
    }
}
