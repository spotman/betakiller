<?php

class Model_ContentPostStatus extends Status_Model
{
    const INCOMPLETE_ID = 1;
    const PENDING_ID = 2;
    const PUBLISHED_ID = 3;
    const PAUSED_ID = 4;

    protected $_table_name = 'content_post_statuses';

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
    protected function get_related_model_fk()
    {
        return 'post_id';
    }
}
