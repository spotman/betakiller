<?php

use BetaKiller\Status\StatusTransitionModelOrm;

class Model_ContentCommentStatusTransition extends StatusTransitionModelOrm
{
    const MARK_AS_SPAM       = 'markAsSpam';
    const APPROVE            = 'approve';
    const MOVE_TO_TRASH      = 'moveToTrash';
    const REJECT             = 'reject';
    const RESTORE_FROM_TRASH = 'restoreFromTrash';

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
