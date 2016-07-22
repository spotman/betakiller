<?php

class Model_AdminAttachmentFile extends Model_AdminContentFile
{
    protected function get_file_table_name()
    {
        return 'admin_content_attachments';
    }

    /**
     * Returns assets provider associated with current model
     *
     * @return Assets_Provider|Assets_Provider_Image
     */
    protected function get_provider()
    {
        return Assets_Provider_Factory::instance()->create('AdminAttachment');
    }
}
