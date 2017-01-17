<?php

class Model_ContentPostStatusTransition extends Status_Transition_Model
{
    protected $_table_name = 'content_post_status_transitions';

    /**
     * @return string
     */
    protected function get_node_model_name()
    {
        return 'ContentPostStatus';
    }
}
