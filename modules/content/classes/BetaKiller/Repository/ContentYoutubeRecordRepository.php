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

    public function findRecordByYoutubeEmbedUrl(string $url): ContentYoutubeRecord
    {
        $id = $this->getYoutubeIdFromEmbedUrl($url);
        return $this->findByYoutubeID($id);
    }

        /**
     * @param string $id
     *
     * @return ContentYoutubeRecord|null
     */
    public function findByYoutubeID(string $id): ?ContentYoutubeRecord
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
    private function filter_youtube_id(OrmInterface $orm, string $value): void
    {
        $orm->where('youtube_id', '=', $value);
    }

    public function getYoutubeIdFromEmbedUrl(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);

        if ( strpos($path, 'embed') === null)
            throw new RepositoryException('No embed in URL :url', [':url' => $url]);

        return basename($path);
    }
}
