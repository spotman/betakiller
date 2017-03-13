<?php

class Model_ContentAttachmentEntity extends Model_ContentEntityWithElements
{
    /**
     * Returns model name which describes files (images or attachments)
     *
     * @return string
     */
    protected function get_element_model_name()
    {
        return 'ContentAttachmentElement';
    }

    /**
     * Returns relation key for files model
     *
     * @return string
     */
    protected function get_element_relation_key()
    {
        return 'attachments';
    }
}
