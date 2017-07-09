<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\ContentYoutubeRecord;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;

/**
 * Class ContentYoutubeRecordRepository
 *
 * @package BetaKiller\Content
 * @method ContentYoutubeRecord findById(int $id)
 * @method ContentYoutubeRecord create()
 * @method ContentYoutubeRecord[] getAll()
 */
class ContentYoutubeRecordRepository extends AbstractOrmBasedRepository implements ContentElementRepositoryInterface
{
    use OrmBasedContentElementRepositoryTrait;

    public function findRecordByYoutubeEmbedUrl(string $url): ?ContentYoutubeRecord
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

        $this->filterYoutubeID($orm, $id);

        $model = $orm->find();

        return $model->loaded() ? $model : null;
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     * @param string                                    $value
     */
    private function filterYoutubeID(OrmInterface $orm, string $value): void
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
