<?php

use BetaKiller\Status\StatusModelOrm;

class Model_ContentPostStatus extends StatusModelOrm
{
    const DRAFT_ID         = 1;
    const PENDING_ID       = 2;
    const PUBLISHED_ID     = 3;
    const PAUSED_ID        = 4;
    const FIX_REQUESTED_ID = 5;

    protected function _initialize()
    {
        $this->_table_name = 'content_post_statuses';

        parent::_initialize();
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
