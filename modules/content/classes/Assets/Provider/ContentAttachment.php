<?php

use BetaKiller\Content\ContentElementInterface;

class Assets_Provider_ContentAttachment extends \Assets_Provider
{
    use \Assets_Provider_ContentTrait;

    /**
     * Custom upload processing
     *
     * @param ContentElementInterface $model
     * @param string                  $content
     * @param array                   $_post_data
     * @param string                  $file_path Full path to source file
     * @return string
     */
    protected function _upload($model, $content, array $_post_data, $file_path)
    {
        $this->upload_preprocessor($model, $_post_data);

        return parent::_upload($model, $content, $_post_data, $file_path);
    }

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
     * @return \Assets_ModelInterface
     */
    public function file_model_factory()
    {
        return $this->model_factory_content_attachment_element();
    }

    protected function get_storage_path_name()
    {
        return 'attachments';
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
