<?php

class Model_AdminAttachmentEntity extends Model_AdminContentEntityRelated
{
    /**
     * Returns model name which describes files (images or attachments)
     *
     * @return string
     */
    protected function get_file_model_name()
    {
        return 'AdminAttachmentFile';
    }

    /**
     * Returns relation key for files model
     *
     * @return string
     */
    protected function get_file_relation_key()
    {
        return 'attachments';
    }
}
