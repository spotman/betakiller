<?php

class Model_AdminYoutubeEntity extends Model_AdminContentEntityRelated
{
    /**
     * Returns model name which describes files (images or attachments)
     *
     * @return string
     */
    protected function get_file_model_name()
    {
        return 'AdminYoutubeRecord';
    }

    /**
     * Returns relation key for files model
     *
     * @return string
     */
    protected function get_file_relation_key()
    {
        return 'videos';
    }
}
