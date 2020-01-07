<?php

namespace BetaKiller\Model;

use BetaKiller\Workflow\AbstractWorkflowStateOrm;

class ContentPostStatus extends AbstractWorkflowStateOrm
{
    public const DRAFT_ID         = 1;
    public const PENDING_ID       = 2;
    public const PUBLISHED_ID     = 3;
    public const PAUSED_ID        = 4;
    public const FIX_REQUESTED_ID = 5;

    protected function configure(): void
    {
        $this->_table_name = 'content_post_statuses';
    }

    /**
     * Returns name of I18n key to proceed
     *
     * @return string
     */
    public function getI18nKeyName(): string
    {
        return 'content-post-state.'.$this->getCodename();
    }
}
