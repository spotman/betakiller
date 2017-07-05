<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\ContentYoutubeRecord;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;

class ContentYoutubeRecordRepository extends AbstractOrmBasedRepository
{
    use \Model_ORM_ContentElementRepositoryTrait;

    /**
     * Creates empty entity
     *
     * @return ContentYoutubeRecord
     */
    public function create(): ContentYoutubeRecord
    {
        return parent::create();
    }

    /**
     * @param string $id
     *
     * @return ContentYoutubeRecord|null
     */
    public function find_by_youtube_id(string $id): ?ContentYoutubeRecord
    {
        /** @var \BetaKiller\Model\ContentYoutubeRecord $orm */
        $orm   = $this->getOrmInstance();

        $this->filter_youtube_id($orm, $id);

        $model = $orm->find();

        return $model->loaded() ? $model : null;
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     * @param string                                    $value
     */
    private function filter_youtube_id(OrmInterface $orm, string $value)
    {
        $orm->where('youtube_id', '=', $value);
    }
}
