<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\ContentCommentStatus;

/**
 * Class ContentCommentStatusRepository
 *
 * @package BetaKiller\Content
 * @method ContentCommentStatus|null findById(int $id)
 * @method ContentCommentStatus|null findByWpID(int $id)
 * @method ContentCommentStatus create()
 * @method ContentCommentStatus[] getAll()
 */
class ContentCommentStatusRepository extends AbstractOrmBasedDispatchableRepository
{
    /**
     * @return string
     */
    public function getUrlKeyName(): string
    {
        return 'codename';
    }

    /**
     * @return \BetaKiller\Model\ContentCommentStatus
     */
    public function getPendingStatus(): ContentCommentStatus
    {
        return $this->findById(ContentCommentStatus::STATUS_PENDING);
    }

    /**
     * @return ContentCommentStatus
     */
    public function getApprovedStatus(): ContentCommentStatus
    {
        return $this->findById(ContentCommentStatus::STATUS_APPROVED);
    }


    /**
     * @return ContentCommentStatus
     */
    public function getSpamStatus(): ContentCommentStatus
    {
        return $this->findById(ContentCommentStatus::STATUS_SPAM);
    }

    /**
     * @return ContentCommentStatus
     */
    public function getTrashStatus(): ContentCommentStatus
    {
        return $this->findById(ContentCommentStatus::STATUS_TRASH);
    }
}
