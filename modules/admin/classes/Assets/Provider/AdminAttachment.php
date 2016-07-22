<?php

class Assets_Provider_AdminAttachment extends \Assets_Provider
{
    use \Assets_Provider_AdminContentTrait;

    /**
     * Returns list of allowed MIME-types (or TRUE if all MIMEs are allowed)
     *
     * @return array|TRUE
     */
    public function get_allowed_mime_types()
    {
        return TRUE;
    }

    /**
     * Creates empty file model
     *
     * @return \Assets_Model
     */
    protected function file_model_factory()
    {
        return $this->model_factory_admin_attachment_file();
    }

    protected function get_storage_path_name()
    {
        return 'attachments';
    }
}
