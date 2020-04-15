<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\ContentCommentState;

interface ContentCommentStateRepositoryInterface extends WorkflowStateRepositoryInterface
{
    /**
     * @return \BetaKiller\Model\ContentCommentState
     */
    public function getPendingStatus(): ContentCommentState;

    /**
     * @return ContentCommentState
     */
    public function getApprovedStatus(): ContentCommentState;


    /**
     * @return ContentCommentState
     */
    public function getSpamStatus(): ContentCommentState;

    /**
     * @return ContentCommentState
     */
    public function getTrashStatus(): ContentCommentState;
}
