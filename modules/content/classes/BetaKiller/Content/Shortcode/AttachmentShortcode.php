<?php
namespace BetaKiller\Content\Shortcode;

use BetaKiller\Content\Shortcode\Attribute\ClassAttribute;
use BetaKiller\Content\Shortcode\Attribute\NumberAttribute;
use BetaKiller\Content\Shortcode\Attribute\TextAttribute;
use BetaKiller\Helper\AssetsHelper;
use BetaKiller\Model\EntityModelInterface;
use BetaKiller\Repository\ContentAttachmentRepository;
use BetaKiller\Repository\ContentImageRepository;

class AttachmentShortcode extends AbstractContentElementShortcode
{
    private const ATTR_LABEL    = 'label';
    private const ATTR_TITLE    = 'title';
    private const ATTR_IMAGE_ID = 'image-id';

    private const LAYOUT_TEXT   = 'text';
    private const LAYOUT_IMAGE  = 'image';
    private const LAYOUT_BUTTON = 'button';

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
     * @param \BetaKiller\Content\Shortcode\ShortcodeEntityInterface $entity
     * @param \BetaKiller\Repository\ContentAttachmentRepository     $repository
     * @param \BetaKiller\Repository\ContentImageRepository          $imageRepository
     * @param \BetaKiller\Helper\AssetsHelper                        $helper
     */
    public function __construct(
        ShortcodeEntityInterface $entity,
        ContentAttachmentRepository $repository,
        ContentImageRepository $imageRepository,
        AssetsHelper $helper
    ) {
        $this->attachmentRepository = $repository;
        $this->imageRepository      = $imageRepository;
        $this->assetsHelper         = $helper;

        parent::__construct($entity);
    }

    /**
     * @return string[]
     */
    protected function getAvailableLayouts(): array
    {
        return [
            self::LAYOUT_BUTTON,
            self::LAYOUT_IMAGE,
            self::LAYOUT_TEXT,
        ];
    }

    /**
     * @return \BetaKiller\Content\Shortcode\Attribute\ShortcodeAttributeInterface[]
     */
    protected function getContentElementShortcodeDefinitions(): array
    {
        return [
            new TextAttribute(self::ATTR_LABEL),
            new TextAttribute(self::ATTR_TITLE, true),
            new ClassAttribute(true),
            new NumberAttribute(self::ATTR_IMAGE_ID, true),
        ];
    }

    /**
     * @return string
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function getWysiwygPluginPreviewSrc(): string
    {
        $id = $this->getID();

        if (!$id) {
            throw new ShortcodeException('Missing ID for :name tag', [':name' => $this->getTagName()]);
        }

        // Check attachment for existence
        $this->attachmentRepository->findById($id);

        $layout = $this->getLayout();

        switch ($layout) {
            case self::LAYOUT_BUTTON:
                return '/assets/static/images/download-button.png';

            case self::LAYOUT_IMAGE:
                return $this->getImageUrl();

            case self::LAYOUT_TEXT:
                return '/assets/static/images/download-text.png';
        }

        throw new ShortcodeException('Unknown [:name] shortcode layout: :value', [
            ':name'  => $this->getTagName(),
            ':value' => $layout,
        ]);
    }

    /**
     * @return array
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function getWidgetData(): array
    {
        $attachID = (int)$this->getID();

        if (!$attachID) {
            throw new ShortcodeException('No attachment ID provided');
        }

        $model = $this->attachmentRepository->findById($attachID);

        $title = \HTML::chars($this->getAttribute('title'));
        $class = $this->getAttribute('class');

        $i18nParams = [
            ':name' => $model->getOriginalName(),
        ];

        $imageUrl = $this->isLayout(self::LAYOUT_IMAGE)
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

    /**
     * @return string
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    private function getImageUrl(): string
    {
        $id = $this->getAttribute(self::ATTR_IMAGE_ID);

        if (!$id) {
            throw new ShortcodeException('Missing image_id attribute');
        }

        $image = $this->imageRepository->findById($id);

        return $this->assetsHelper->getOriginalUrl($image);
    }

    /**
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function useButtonLayout(): void
    {
        $this->setLayout(self::LAYOUT_BUTTON);
        $this->setAttribute(self::ATTR_LABEL, null);
        $this->setAttribute(self::ATTR_IMAGE_ID, null);
    }

    /**
     * @param int $imageID
     *
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function useImageLayout(int $imageID): void
    {
        $this->setLayout(self::LAYOUT_IMAGE);
        $this->setImageID($imageID);
    }

    /**
     * @param string $label
     *
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function useTextLayout(string $label): void
    {
        $this->setLayout(self::LAYOUT_TEXT);
        $this->setAttribute(self::ATTR_LABEL, $label);
        $this->setAttribute(self::ATTR_IMAGE_ID, null);
    }

    /**
     * @param int $value
     *
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    protected function setImageID(int $value): void
    {
        $this->setAttribute(self::ATTR_IMAGE_ID, $value);
    }

    /**
     * @param \BetaKiller\Model\EntityModelInterface|null $relatedEntity
     * @param int|null                                    $itemID
     *
     * @return \BetaKiller\Content\Shortcode\Editor\EditorListingItem[]
     */
    public function getEditorListingItems(?EntityModelInterface $relatedEntity, ?int $itemID): array
    {
        // TODO: Implement getEditorListingItems() method.
        return [];
    }
}
