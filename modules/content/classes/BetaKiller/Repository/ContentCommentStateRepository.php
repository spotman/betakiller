<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\ContentCommentState;

/**
 * Class ContentCommentStateRepository
 *
 * @package BetaKiller\Content
 * @method ContentCommentState|null findById(int $id)
 * @method ContentCommentState create()
 * @method ContentCommentState[] getAll()
 */
class ContentCommentStateRepository extends AbstractWorkflowStateRepository implements
    ContentCommentStateRepositoryInterface
{
    /**
     * @return string
     */
    public function getUrlKeyName(): string
    {
        return ContentCommentState::COL_CODENAME;
    }

    /**
     * @return \BetaKiller\Model\ContentCommentState
     */
    public function getPendingStatus(): ContentCommentState
    {
        return $this->getByCodename(ContentCommentState::PENDING);
    }

    /**
     * @return ContentCommentState
     */
    public function getApprovedStatus(): ContentCommentState
    {
        return $this->getByCodename(ContentCommentState::APPROVED);
    }

    /**
     * @return ContentCommentState
     */
    public function getSpamStatus(): ContentCommentState
    {
        return $this->getByCodename(ContentCommentState::SPAM);
    }

    /**
     * @return ContentCommentState
     */
    public function getTrashStatus(): ContentCommentState
    {
        return $this->getByCodename(ContentCommentState::TRASH);
    }
}
