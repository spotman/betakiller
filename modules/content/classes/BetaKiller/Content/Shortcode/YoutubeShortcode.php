<?php
namespace BetaKiller\Content\Shortcode;

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
        $id    = (int)$this->getID();
        $model = $this->getRecordById($id);

        return $model->getPreviewUrl();
    }

    private function getRecordById(int $id): ContentYoutubeRecord
    {
        return $this->repository->findById($id);
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
