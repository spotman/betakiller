<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\ContentCommentInterface;
use BetaKiller\Model\ContentCommentState;
use BetaKiller\Model\Entity;
use BetaKiller\Model\EntityModelInterface;

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
interface ContentCommentRepositoryInterface extends DispatchableRepositoryInterface,
    HasWorkflowStateRepositoryInterface, SingleParentTreeRepositoryInterface,
    EntityItemRelatedRepositoryInterface, RepositoryHasWordpressIdInterface
{
    /**
     * @param string   $ipAddress
     * @param int|null $interval
     *
     * @return int
     * @throws \Exception
     */
    public function getCommentsCountForIP(string $ipAddress, ?int $interval = null): int;

    /**
     * @param \BetaKiller\Model\EntityModelInterface $entity
     * @param int                                    $entityItemID
     *
     * @return \BetaKiller\Model\ContentCommentInterface[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getEntityItemApprovedComments(EntityModelInterface $entity, int $entityItemID): array;

    /**
     * @param \BetaKiller\Model\ContentCommentState|null $status
     * @param \BetaKiller\Model\EntityModelInterface     $entity
     * @param int|null                                   $entityItemId
     *
     * @return ContentCommentInterface[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getCommentsOrderedByPath(
        ContentCommentState $status,
        EntityModelInterface $entity,
        int $entityItemId
    ): array;

    /**
     * @param \BetaKiller\Model\ContentCommentState|null $status
     *
     * @return ContentCommentInterface[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getLatestComments(?ContentCommentState $status = null): array;

    /**
     * @param \BetaKiller\Model\ContentCommentState|null $status
     * @param \BetaKiller\Model\Entity|null              $entity
     * @param int|null                                   $entityItemId
     *
     * @return int
     */
    public function getCommentsCount(
        ?ContentCommentState $status = null,
        ?Entity $entity = null,
        ?int $entityItemId = null
    ): int;
}
