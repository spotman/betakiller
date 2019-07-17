<?php
namespace BetaKiller\Model;

use BetaKiller\Workflow\StatusTransitionModelOrm;

class ContentCommentStatusTransition extends StatusTransitionModelOrm
{
    protected function configure(): void
    {
        $this->_table_name = 'content_comment_status_transitions';

        parent::configure();
    }

    /**
     * @return string
     */
    protected function getNodeModelName(): string
    {
        return 'ContentCommentStatus';
    }
}
