<?php

class Model_ContentPostStatus extends Status_Model
{
    const DRAFT_ID = 1;
    const PUBLISHED_ID = 2;
    const PAUSED_ID = 3;

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
