<?php
namespace BetaKiller\Content\Shortcode;

use BetaKiller\Content\Shortcode\Editor\EditorListingItem;
use BetaKiller\Model\ContentYoutubeRecord;
use BetaKiller\Model\EntityModelInterface;
use BetaKiller\Repository\ContentYoutubeRecordRepository;

class YoutubeShortcode extends AbstractContentElementShortcode
{
    /**
     * @var \BetaKiller\Repository\ContentYoutubeRecordRepository
     */
    private $repository;

    /**
     * YoutubeShortcode constructor.
     *
     * @param \BetaKiller\Content\Shortcode\ShortcodeEntityInterface $entity
     * @param \BetaKiller\Repository\ContentYoutubeRecordRepository  $repository
     */
    public function __construct(ShortcodeEntityInterface $entity, ContentYoutubeRecordRepository $repository)
    {
        $this->repository = $repository;

        parent::__construct($entity);
    }

    /**
     * @return \BetaKiller\Content\Shortcode\Attribute\ShortcodeAttributeInterface[]
     */
    protected function getContentElementShortcodeDefinitions(): array
    {
        // No attributes needed
        return [];
    }

    /**
     * @return string[]
     */
    protected function getAvailableLayouts(): array
    {
        // No layouts
        return [];
    }

    /**
     * @return array
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function getWidgetData(): array
    {
        $videoID = (int)$this->getID();

        if (!$videoID) {
            throw new ShortcodeException('No YouTube ID provided');
        }

        $model = $this->repository->findById($videoID);

        return [
            'video' => [
                'src'    => $model->getYoutubeEmbedUrl(),
                'width'  => $model->getWidth(),
                'height' => $model->getHeight(),
            ],
        ];
    }

    /**
     * @return string
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function getWysiwygPluginPreviewSrc(): string
    {
        $model = $this->getCurrentModel();

        return $model->getPreviewUrl();
    }

    /**
     * @return \BetaKiller\Model\ContentYoutubeRecord
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    private function getCurrentModel(): ContentYoutubeRecord
    {
        $id = (int)$this->getID();
        return $this->repository->findById($id);
    }

    /**
     * @param \BetaKiller\Model\EntityModelInterface|null $relatedEntity
     * @param int|null                                    $itemID
     *
     * @return \BetaKiller\Content\Shortcode\Editor\EditorListingItem[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getEditorListingItems(?EntityModelInterface $relatedEntity, ?int $itemID): array
    {
        /** @var ContentYoutubeRecord[] $records */
        $records = $this->repository->getEditorListing($relatedEntity, $itemID);

        $data = [];

        foreach ($records as $record) {
            $data[] = new EditorListingItem(
                $record->getID(),
                $record->getYoutubeId(),
                $record->isValid(),
                $record->getPreviewUrl()
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
        $model = $this->getCurrentModel();

        // No data for editing
        return [
            'youtubeID' => $model->getYoutubeId(),
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
        $model = $this->getCurrentModel();

        if (isset($data['youtubeID'])) {
            $youtubeID = \trim(\HTML::entities($data['youtubeID']));
            $model->setYoutubeId($youtubeID);
        }

        try {
            $this->repository->save($model);
        } catch (\ORM_Validation_Exception $e) {
            throw new ShortcodeException(':error', [':error' => implode(', ', $e->getFormattedErrors())]);
        }
    }

    /**
     * Return url for uploading new items or null if items can not be uploaded and must be added via regular edit form
     *
     * @return null|string
     */
    public function getEditorItemUploadUrl(): ?string
    {
        // No upload allowed
        return null;
    }

    /**
     * Return array of allowed mime-types
     *
     * @return string[]
     */
    public function getEditorItemAllowedMimeTypes(): array
    {
        // No files here
        return [];
    }
}
