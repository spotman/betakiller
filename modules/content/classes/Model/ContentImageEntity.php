<?php

class Model_ContentImageEntity extends Model_EntityWithElements
{
    /**
     * Returns model name which describes files (images or attachments)
     *
     * @return string
     */
    protected function get_element_model_name()
    {
        return 'ContentImageElement';
    }

    /**
     * Returns relation key for files model
     *
     * @return string
     */
    protected function get_element_relation_key()
    {
        return 'images';
    }
}
