<?php
namespace BetaKiller\Content\Shortcode;

use BetaKiller\Content\Shortcode\Attribute\ItemAttribute;
use BetaKiller\Content\Shortcode\Attribute\StringAttribute;
use BetaKiller\Content\Shortcode\Editor\ContentElementShortcodeEditor;
use BetaKiller\Content\Shortcode\Editor\EditorListingItem;
use BetaKiller\Helper\AssetsHelper;
use BetaKiller\Model\ContentImageInterface;
use BetaKiller\Model\EntityModelInterface;
use BetaKiller\Repository\ContentAttachmentRepository;
use BetaKiller\Repository\ContentImageRepository;

class AttachmentShortcode extends AbstractContentElementShortcode
{
    private const ATTR_LABEL    = 'label';
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
            (new StringAttribute(self::ATTR_LABEL))
                ->dependsOn(self::ATTR_LAYOUT, self::LAYOUT_TEXT),

            // Select image
            (new ItemAttribute(self::ATTR_IMAGE_ID, 'Image'))
                ->dependsOn(self::ATTR_LAYOUT, self::LAYOUT_IMAGE),
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
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Assets\Exception\AssetsStorageException
     * @throws \BetaKiller\Assets\Exception\AssetsException
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function getWidgetData(): array
    {
        $attachID = (int)$this->getID();

        if (!$attachID) {
            throw new ShortcodeException('No attachment ID provided');
        }

        $model = $this->attachmentRepository->findById($attachID);

        $imageData = $this->isLayout(self::LAYOUT_IMAGE)
            ? $this->getImageData()
            : null;

        return [
            'name'   => $model->getOriginalName(),
            'image'  => $imageData,
            'label'  => $this->getAttribute(self::ATTR_LABEL),
            'url'    => $this->assetsHelper->getDownloadUrl($model),
            'layout' => $this->getLayout(),
        ];
    }

    /**
     * @return array
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Assets\Exception\AssetsStorageException
     * @throws \BetaKiller\Assets\Exception\AssetsException
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    private function getImageData(): array
    {
        $image = $this->getImage();

        return $this->assetsHelper->getAttributesForImgTag($image, $image::SIZE_ORIGINAL);
    }

    /**
     * @return string
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    private function getImageUrl(): string
    {
        $image = $this->getImage();

        return $this->assetsHelper->getOriginalUrl($image);
    }

    /**
     * @return \BetaKiller\Model\ContentImageInterface
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    private function getImage(): ContentImageInterface
    {
        $id = $this->getAttribute(self::ATTR_IMAGE_ID);

        if (!$id) {
            throw new ShortcodeException('Missing image_id attribute');
        }

        return $this->imageRepository->findById($id);
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
        $this->setAttribute(self::ATTR_LABEL, null);
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
        $attachments = $this->attachmentRepository->getEditorListing($relatedEntity, $itemID);

        $data = [];

        foreach ($attachments as $attach) {
            $data[] = new EditorListingItem(
                $attach->getID(),
                $attach->getOriginalName(),
                $attach->isValid(),
                null, // No image, detect icon from mime-type
                $attach->getMime()
            );
        }

        return $data;
    }

    /**
     * Returns item data (based on "id" attribute value)
     *
     * @return array
     */
    public function getEditorItemData(): array
    {
        // No data for editing
        return [];
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
        return $this->assetsHelper->getUploadUrl($this->attachmentRepository::getCodename());
    }

    /**
     * Return array of allowed mime-types
     *
     * @return string[]
     */
    public function getEditorItemAllowedMimeTypes(): array
    {
        // All mime-types allowed
        return [
            ContentElementShortcodeEditor::ANY_MIME_TYPES,
        ];
    }
}
