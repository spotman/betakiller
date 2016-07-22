<?php

class Assets_Provider_AdminImage extends \Assets_Provider_Image
{
    use \Assets_Provider_AdminContentTrait;

    protected function get_storage_path_name()
    {
        return 'images';
    }

    /**
     * @return int
     */
    public function get_upload_max_height()
    {
        // TODO: Implement get_upload_max_height() method.
    }

    /**
     * @return int
     */
    public function get_upload_max_width()
    {
        // TODO: Implement get_upload_max_width() method.
    }

    /**
     * Defines allowed sizes for previews
     * Returns array of strings like this
     *
     * array('300x200', '75x75', '400x', 'x250')
     *
     * @return array
     */
    public function get_allowed_preview_sizes()
    {
        // TODO: Implement get_allowed_preview_sizes() method.
    }

    /**
     * Defines allowed sizes for cropping
     * Returns array of strings like this
     *
     * array('300x200', '75x75')
     *
     * @return array|NULL
     */
    public function get_allowed_crop_sizes()
    {
        // TODO: Implement get_allowed_crop_sizes() method.
    }

    /**
     * Returns list of allowed MIME-types (or TRUE if all MIMEs are allowed)
     *
     * @return array|TRUE
     */
    public function get_allowed_mime_types()
    {
        return [
            'image/jpeg',
            'image/png',
            'image/gif',
        ];
    }

    /**
     * Creates empty file model
     *
     * @return Assets_Model
     */
    protected function file_model_factory()
    {
        return $this->model_factory_admin_image_file();
    }
}
