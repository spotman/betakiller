<?php

use BetaKiller\Status\StatusModelOrm;

class Model_ContentCommentStatus extends StatusModelOrm
{
    const STATUS_PENDING = 1;
    const STATUS_SPAM = 3;
    const STATUS_DELETED = 4;
    const STATUS_APPROVED = 2;
    protected $_table_name = 'content_comment_statuses';

    /**
     * @return string
     */
    protected function get_related_model_key()
    {
        return 'status';
    }

    /**
     * @return string
     */
    protected function get_related_model_name()
    {
        return 'ContentComment';
    }

    /**
     * @return string
     */
    protected function get_transition_model_name()
    {
        return 'ContentCommentStatusTransition';
    }

    /**
     * @return string
     */
    protected function get_related_model_fk()
    {
        return 'status_id';
    }
}
