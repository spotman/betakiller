<?php

use BetaKiller\Status\StatusTransitionModelOrm;

class Model_ContentPostStatusTransition extends StatusTransitionModelOrm
{
    protected function _initialize()
    {
        $this->_table_name = 'content_post_status_transitions';

        parent::_initialize();
    }

    /**
     * @return string
     */
    protected function getNodeModelName(): string
    {
        return 'ContentPostStatus';
    }
}
