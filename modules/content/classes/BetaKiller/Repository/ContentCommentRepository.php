<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\ContentComment;
use BetaKiller\Model\ContentCommentInterface;
use BetaKiller\Model\ContentCommentStatus;
use BetaKiller\Model\Entity;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;
use DateInterval;
use DateTime;

/**
 * Class ContentCommentRepository
 *
 * @package BetaKiller\Content
 *
 * @method ContentCommentInterface|null findById(int $id)
 * @method ContentCommentInterface|null findByWpID(int $id)
 * @method ContentCommentInterface create()
 * @method ContentCommentInterface[] getAll()
 */
class ContentCommentRepository extends AbstractOrmBasedDispatchableRepository
    implements EntityModelRelatedRepositoryInterface, RepositoryHasWordpressIdInterface
{
    use OrmBasedRepositoryHasWordpressIdTrait;
    use OrmBasedEntityRelatedRepositoryTrait;

    /**
     * @Inject
     * @var \BetaKiller\Repository\ContentCommentStatusRepository
     */
    private $commentStatusRepository;

    /**
     * @return string
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getUrlKeyName(): string
    {
        throw new RepositoryException('No search for content comment allowed by url key');
    }

    /**
     * @param string $ipAddress
     * @param int|null $interval
     *
     * @return int
     */
    public function getCommentsCountForIP(string $ipAddress, ?int $interval = null): int
    {
        $interval = $interval ?: 30;

        $orm = $this->getOrmInstance();
        $key = 'PT'.(int)$interval.'S';

        $this
            ->filterLastRecords($orm, new \DateInterval($key))
            ->filterIpAddress($orm, $ipAddress);

        return $orm->count_all();
    }

    /**
     * @param \BetaKiller\Model\Entity $entity
     * @param int                      $entityItemID
     *
     * @return \BetaKiller\Model\ContentComment[]
     */
    public function getEntityItemApprovedComments(Entity $entity, int $entityItemID): array
    {
        /** @var \BetaKiller\Model\ContentCommentStatus $status */
        $status = $this->commentStatusRepository->getApprovedStatus();

        return $this->getCommentsOrderedByPath($status, $entity, $entityItemID);
    }

    /**
     * @param \BetaKiller\Model\ContentCommentStatus|null $status
     * @param \BetaKiller\Model\Entity|null               $entity
     * @param int|null                                    $entityItemID
     *
     * @return ContentComment[]
     */
    public function getCommentsOrderedByPath(
        ?ContentCommentStatus $status = null,
        ?Entity $entity = null,
        ?int $entityItemID = null
    ): array {
        /** @var \BetaKiller\Model\ContentComment $model */
        $model = $this->getOrmInstance();

        if ($status) {
            $model->filter_status($status);
        }

        $this->filterEntityAndEntityItemID($model, $entity, $entityItemID);

        $this->orderByPath($model);

        return $model->get_all();
    }

    /**
     * @param \BetaKiller\Model\ContentCommentStatus|null $status
     *
     * @return ContentComment[]
     */
    public function getLatestComments(?ContentCommentStatus $status = null): array
    {
        /** @var \BetaKiller\Model\ContentComment $model */
        $model = $this->getOrmInstance();

        if ($status) {
            $model->filter_status($status);
        }

        $this->orderByCreatedAt($model);

        return $model->get_all();
    }

    /**
     * @param \BetaKiller\Model\ContentCommentStatus|null $status
     * @param \BetaKiller\Model\Entity|null               $entity
     * @param int|null                                    $entityItemId
     *
     * @return int
     */
    public function getCommentsCount(
        ?ContentCommentStatus $status = null,
        ?Entity $entity = null,
        ?int $entityItemId = null
    ): int {
        /** @var \BetaKiller\Model\ContentComment $orm */
        $orm = $this->getOrmInstance();

        $this->filterEntityAndEntityItemID($orm, $entity, $entityItemId);

        if ($status) {
            $orm->filter_status($status);
        }

        return $orm->compile_as_subquery_and_count_all();
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface|mixed $entity
     */
    public function delete($entity): void
    {
        // TODO Delete child comments

        parent::delete($entity);
    }

//    private function filter_pending()
//    {
//        return $this->filter_status_id(ContentCommentStatus::STATUS_PENDING);
//    }
//
//    private function filter_approved()
//    {
//        return $this->filter_status_id(ContentCommentStatus::STATUS_APPROVED);
//    }
//
//    private function filter_spam()
//    {
//        return $this->filter_status_id(ContentCommentStatus::STATUS_SPAM);
//    }
//
//    private function filter_trash()
//    {
//        return $this->filter_status_id(ContentCommentStatus::STATUS_TRASH);
//    }
//
//    private function filter_status_id(ContentCommentStatus $orm, int $id)
//    {
//
//    }
//
//    /**
//     * @return int
//     */
//    public function get_pending_comments_count()
//    {
//        /** @var \ContentCommentStatus $statusOrm */
//        $statusOrm = $this->status_model_factory();
//        $status    = $statusOrm->getPendingStatus();
//
//        return $this->getCommentsCount($status);
//    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     * @param \DateInterval                             $interval
     *
     * @return $this
     */
    private function filterLastRecords(OrmInterface $orm, DateInterval $interval)
    {
        $time = new DateTime();
        $time->sub($interval);

        $orm->filter_datetime_column_value($orm->object_column('created_at'), $time, '>');

        return $this;
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     * @param string                                    $value
     *
     * @return $this
     */
    private function filterIpAddress(OrmInterface $orm, string $value)
    {
        $orm->where($orm->object_column('ip_address'), '=', (string)$value);

        return $this;
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     *
     * @return $this
     */
    private function orderByPath(OrmInterface $orm)
    {
        $orm->order_by('path', 'asc');

        return $this;
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     * @param bool|null                                 $asc
     *
     * @return $this
     */
    private function orderByCreatedAt(OrmInterface $orm, ?bool $asc = null)
    {
        $orm->order_by('created_at', $asc ? 'asc' : 'desc');

        return $this;
    }
}
