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
    protected function get_related_model_key(): string
    {
        return 'status';
    }

    /**
     * @return string
     */
    protected function get_related_model_name(): string
    {
        return 'ContentPost';
    }

    /**
     * @return string
     */
    protected function get_transition_model_name()
    {
        return 'ContentPostStatusTransition';
    }

    /**
     * @return string
     */
    protected function get_related_model_fk(): string
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
