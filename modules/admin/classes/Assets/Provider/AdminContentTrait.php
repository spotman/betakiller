<?php

trait Assets_Provider_AdminContentTrait
{
    use \BetaKiller\Helper\Admin;


    /**
     * Custom upload processing
     *
     * @param Model_AdminImageFile|Model_AdminAttachmentFile $model
     * @param string $content
     * @param array $_post_data
     * @return string
     */
    protected function _upload($model, $content, array $_post_data)
    {
        $entity_id = (int) Arr::get($_post_data, 'entityID') ?: NULL;
        $entity_item_id = (int) Arr::get($_post_data, 'entityItemID') ?: NULL;

        if ($entity_id)
        {
            $entity = $this->model_factory_admin_content_entity($entity_id);
            $model->set_entity($entity);
        }

        if ($entity_item_id)
        {
            $model->set_entity_item_id($entity_item_id);
        }

        return $content;
    }

    /**
     * Returns concrete storage for current provider
     *
     * @return \Assets_Storage
     */
    protected function storage_factory()
    {
        $assets_path = MultiSite::instance()->site_path().DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR;

        /** @var \Assets_Storage_Local $storage */
        $storage = Assets_Storage_Factory::instance()->create('Local');

        return $storage->set_base_path($assets_path.$this->get_storage_path_name());
    }

    abstract protected function get_storage_path_name();


    /**
     * Returns TRUE if upload is granted
     *
     * @return bool
     */
    protected function check_upload_permissions()
    {
        // TODO: Implement check_upload_permissions() method.
        return TRUE;
    }

    /**
     * Returns TRUE if deploy is granted
     *
     * @param \Assets_Model $model
     * @return bool
     */
    protected function check_deploy_permissions($model)
    {
        // TODO: Implement check_deploy_permissions() method.
        return TRUE;
    }

    /**
     * Returns TRUE if delete operation granted
     *
     * @param Assets_Model $model
     * @return bool
     */
    protected function check_delete_permissions($model)
    {
        // TODO: Implement check_delete_permissions() method.
        return TRUE;
    }
}
