<?php
namespace BetaKiller\Model;

use BetaKiller\Status\StatusTransitionModelOrm;

class ContentCommentStatusTransition extends StatusTransitionModelOrm
{
    public const MARK_AS_SPAM       = 'markAsSpam';
    public const APPROVE            = 'approve';
    public const MOVE_TO_TRASH      = 'moveToTrash';
    public const REJECT             = 'reject';
    public const RESTORE_FROM_TRASH = 'restoreFromTrash';

    protected function _initialize()
    {
        $this->_table_name = 'content_comment_status_transitions';

        parent::_initialize();
    }

    /**
     * @return string
     */
    protected function getNodeModelName(): string
    {
        return 'ContentCommentStatus';
    }
}
