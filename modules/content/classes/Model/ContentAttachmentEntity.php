<?php

class Model_ContentAttachmentEntity extends Model_ContentEntityRelated
{
    /**
     * Returns model name which describes files (images or attachments)
     *
     * @return string
     */
    protected function get_file_model_name()
    {
        return 'ContentAttachmentElement';
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
