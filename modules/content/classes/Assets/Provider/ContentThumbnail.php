<?php

class Assets_Provider_ContentThumbnail extends \Assets_Provider_Image
{
    use \Assets_Provider_ContentTrait;

    /**
     * Custom upload processing
     *
     * @param Model_ContentImageElement $model
     * @param string $content
     * @param array $_post_data
     * @param string $file_path Full path to source file
     * @return string
     */
    protected function _upload($model, $content, array $_post_data, $file_path)
    {
        $this->upload_preprocessor($model, $_post_data);

        return parent::_upload($model, $content, $_post_data, $file_path);
    }

    protected function get_storage_path_name()
    {
        return 'thumbnails';
    }

    /**
     * @return int
     */
    public function get_upload_max_height()
    {
        return $this->get_assets_provider_config_value(['upload', 'max-height']);
    }

    /**
     * @return int
     */
    public function get_upload_max_width()
    {
        return $this->get_assets_provider_config_value(['upload', 'max-width']);
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
        return $this->get_assets_provider_config_value(['sizes', 'preview']);
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
        return $this->get_assets_provider_config_value(['sizes', 'crop']);
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
     * @return Assets_ModelInterface
     */
    protected function file_model_factory()
    {
        return $this->model_factory_content_post_thumbnail();
    }

    /**
     * Returns TRUE if upload is granted
     *
     * @return bool
     */
    protected function check_upload_permissions()
    {
        // TODO Move to ACL
        return $this->_user AND $this->_user->is_admin_allowed();
    }

    /**
     * Returns TRUE if deploy is granted
     *
     * @param \Assets_ModelInterface $model
     * @return bool
     */
    protected function check_deploy_permissions($model)
    {
        // TODO Move to ACL
        return TRUE;
    }

    /**
     * Returns TRUE if delete operation granted
     *
     * @param Assets_ModelInterface $model
     * @return bool
     */
    protected function check_delete_permissions($model)
    {
        // TODO Move to ACL
        return $this->_user AND $this->_user->is_admin_allowed();
    }
}
