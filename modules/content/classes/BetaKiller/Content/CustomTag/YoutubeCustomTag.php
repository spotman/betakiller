<?php
namespace BetaKiller\Content\CustomTag;

use BetaKiller\Model\ContentYoutubeRecord;
use BetaKiller\Repository\ContentYoutubeRecordRepository;

class YoutubeCustomTag extends AbstractCustomTag
{
    const TAG_NAME = 'youtube';

    /**
     * @var \BetaKiller\Repository\ContentYoutubeRecordRepository
     */
    private $repository;

    /**
     * YoutubeCustomTag constructor.
     *
     * @param \BetaKiller\Repository\ContentYoutubeRecordRepository $repository
     */
    public function __construct(ContentYoutubeRecordRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Returns HTML tag name
     *
     * @return string
     */
    public function getTagName(): string
    {
        return self::TAG_NAME;
    }

    public function getWysiwygPluginPreviewSrc(array $attributes): string
    {
        $id = (int)$attributes['id'];
        $model = $this->getRecordById($id);

        return $model->getPreviewUrl();
    }

    private function getRecordById(int $id): ContentYoutubeRecord
    {
        return $this->repository->findById($id);
    }
}
