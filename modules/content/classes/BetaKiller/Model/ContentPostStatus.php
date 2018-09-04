<?php

namespace BetaKiller\Model;

use BetaKiller\Status\StatusModelOrm;

class ContentPostStatus extends StatusModelOrm
{
    public const DRAFT_ID         = 1;
    public const PENDING_ID       = 2;
    public const PUBLISHED_ID     = 3;
    public const PAUSED_ID        = 4;
    public const FIX_REQUESTED_ID = 5;

    protected function configure(): void
    {
        $this->_table_name = 'content_post_statuses';

        parent::configure();
    }

    /**
     * @return string
     */
    protected function getRelatedModelKey(): string
    {
        return 'status';
    }

    /**
     * @return string
     */
    protected function getRelatedModelName(): string
    {
        return 'ContentPost';
    }

    /**
     * @return string
     */
    protected function getTransitionModelName(): string
    {
        return 'ContentPostStatusTransition';
    }

    /**
     * @return string
     */
    protected function getRelatedModelFk(): string
    {
        return 'post_id';
    }

    /**
     * @return string
     */
    protected function getStatusAclModelName(): string
    {
        return 'ContentPostStatusAcl';
    }

    /**
     * @return string
     */
    protected function getStatusAclModelForeignKey(): string
    {
        return 'status_id';
    }
}
