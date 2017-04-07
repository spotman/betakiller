<?php

use BetaKiller\Status\StatusTransitionModelOrm;

class Model_ContentCommentStatusTransition extends StatusTransitionModelOrm
{
    protected function _initialize()
    {
        $this->_table_name = 'content_comment_status_transitions';

        parent::_initialize();
    }

    /**
     * @return string
     */
    protected function get_node_model_name()
    {
        return 'ContentCommentStatus';
    }
}
