<?php

class Assets_Provider_ContentPostThumbnail extends \Assets_Provider_Image
{
    use \Assets_Provider_ContentTrait;

    protected function get_storage_path_name()
    {
        return 'post-thumbnails';
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
        return $this->get_assets_provider_config_value(['sizes']);
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
    public function file_model_factory()
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
        $user = $this->get_user();

        // TODO Move to ACL
        return $user AND $user->is_admin_allowed();
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
        $user = $this->get_user();

        // TODO Move to ACL
        return $user AND $user->is_admin_allowed();
    }
}
