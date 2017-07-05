<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\ContentCommentStatus;

class ContentCommentStatusRepository extends AbstractOrmBasedRepository
{
    /**
     * Creates empty entity
     *
     * @return mixed
     */
    public function create(): ContentCommentStatus
    {
        return parent::create();
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
