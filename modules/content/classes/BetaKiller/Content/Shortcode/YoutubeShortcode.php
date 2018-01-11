<?php
namespace BetaKiller\Content\Shortcode;

use BetaKiller\Content\Shortcode\Attribute\NumberAttribute;
use BetaKiller\Model\ContentYoutubeRecord;
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
        return [
            new NumberAttribute('width', true),
            new NumberAttribute('height', true),
        ];
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

        $width  = (int)$this->getAttribute('width');
        $height = (int)$this->getAttribute('height');

        return [
            'video' => [
                'src'    => $model->getYoutubeEmbedUrl(),
                'width'  => $width,
                'height' => $height,
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
}
