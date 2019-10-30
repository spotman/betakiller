<?php
namespace BetaKiller\Repository;

use BetaKiller\Factory\OrmFactory;
use BetaKiller\Model\ContentComment;
use BetaKiller\Model\ContentCommentInterface;
use BetaKiller\Model\ContentCommentStatus;
use BetaKiller\Model\Entity;
use BetaKiller\Model\EntityModelInterface;
use BetaKiller\Model\ExtendedOrmInterface;
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
 * @method ContentCommentInterface[] getAll()
 * @method ContentCommentInterface|null getParent(ContentCommentInterface $parent)
 */
class ContentCommentRepository extends AbstractOrmBasedSingleParentTreeRepository
    implements EntityItemRelatedRepositoryInterface, RepositoryHasWordpressIdInterface
{
    use OrmBasedRepositoryHasWordpressIdTrait;
    use OrmBasedEntityItemRelatedRepositoryTrait;

    /**
     * @Inject
     * @var \BetaKiller\Repository\ContentCommentStatusRepository
     */
    private $commentStatusRepository;

    /**
     * AbstractOrmBasedRepository constructor.
     *
     * @param \BetaKiller\Factory\OrmFactory                        $ormFactory
     * @param \BetaKiller\Repository\ContentCommentStatusRepository $commentStatusRepo
     */
    public function __construct(OrmFactory $ormFactory, ContentCommentStatusRepository $commentStatusRepo)
    {
        parent::__construct($ormFactory);

        $this->commentStatusRepository = $commentStatusRepo;
    }

    /**
     * @return string
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getUrlKeyName(): string
    {
        throw new RepositoryException('No search for content comment allowed by url key');
    }

    /**
     * @return string
     */
    protected function getParentIdColumnName(): string
    {
        return 'parent_id';
    }

    /**
     * @param string   $ipAddress
     * @param int|null $interval
     *
     * @return int
     * @throws \Exception
     */
    public function getCommentsCountForIP(string $ipAddress, ?int $interval = null): int
    {
        $interval = $interval ?: 30;

        $orm = $this->getOrmInstance();
        $key = 'PT'.$interval.'S';

        $this
            ->filterLastRecords($orm, new \DateInterval($key))
            ->filterIpAddress($orm, $ipAddress);

        return $orm->count_all();
    }

    /**
     * @param \BetaKiller\Model\EntityModelInterface $entity
     * @param int                                    $entityItemID
     *
     * @return \BetaKiller\Model\ContentComment[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getEntityItemApprovedComments(EntityModelInterface $entity, int $entityItemID): array
    {
        /** @var \BetaKiller\Model\ContentCommentStatus $status */
        $status = $this->commentStatusRepository->getApprovedStatus();

        return $this->getCommentsOrderedByPath($status, $entity, $entityItemID);
    }

    /**
     * @param \BetaKiller\Model\ContentCommentStatus|null $status
     * @param \BetaKiller\Model\EntityModelInterface      $entity
     * @param int|null                                    $entityItemId
     *
     * @return ContentComment[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getCommentsOrderedByPath(
        ContentCommentStatus $status,
        EntityModelInterface $entity,
        int $entityItemId
    ): array {
        /** @var \BetaKiller\Model\ContentComment $orm */
        $orm = $this->getOrmInstance();

        if ($status) {
            $orm->filterStatus($status);
        }

        $this->filterEntityAndEntityItemID($orm, $entity, $entityItemId);

        $this->orderByPath($orm);

        return $this->findAll($orm);
    }

    /**
     * @param \BetaKiller\Model\ContentCommentStatus|null $status
     *
     * @return ContentCommentInterface[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getLatestComments(?ContentCommentStatus $status = null): array
    {
        /** @var \BetaKiller\Model\ContentComment $orm */
        $orm = $this->getOrmInstance();

        if ($status) {
            $orm->filterStatus($status);
        }

        $this->orderByCreatedAt($orm);

        return $this->findAll($orm);
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

        $this->filterEntityOrEntityItemID($orm, $entity, $entityItemId);

        if ($status) {
            $orm->filterStatus($status);
        }

        return $orm->compile_as_subquery_and_count_all();
    }

    /**
     * @param ContentCommentInterface|mixed $entity
     *
     * @throws \BetaKiller\Exception\ValidationException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function save($entity): void
    {
        if (!$entity->getPath()) {
            $levels = [];

            // Combine path from parent IDs
            foreach ($entity->getAllParents() as $parent) {
                $levels[] = $parent->getID();
            }

            // Add root level
            $levels[] = 0;
            $path     = implode('.', array_reverse($levels));

            $entity->setPath($path);
        }

        parent::save($entity);
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface|mixed $entity
     *
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function delete($entity): void
    {
        // TODO Delete child comments

        parent::delete($entity);
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     * @param \DateInterval                             $interval
     *
     * @return $this
     */
    private function filterLastRecords(OrmInterface $orm, DateInterval $interval): self
    {
        $time = new \DateTimeImmutable;
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
    private function filterIpAddress(OrmInterface $orm, string $value): self
    {
        $orm->where($orm->object_column('ip_address'), '=', $value);

        return $this;
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     *
     * @return $this
     */
    private function orderByPath(OrmInterface $orm): self
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
    private function orderByCreatedAt(OrmInterface $orm, ?bool $asc = null): self
    {
        $orm->order_by('created_at', $asc ? 'asc' : 'desc');

        return $this;
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface $orm
     *
     * @return void
     */
    protected function customFilterForTreeTraversing(ExtendedOrmInterface $orm): void
    {
        // Nothing to do here
    }
}
