<?php
namespace BetaKiller\Model;

use BetaKiller\Status\StatusTransitionModelOrm;

class ContentPostStatusTransition extends StatusTransitionModelOrm
{
    protected function configure(): void
    {
        $this->_table_name = 'content_post_status_transitions';

        parent::configure();
    }

    /**
     * @return string
     */
    protected function getNodeModelName(): string
    {
        return 'ContentPostStatus';
    }
}
