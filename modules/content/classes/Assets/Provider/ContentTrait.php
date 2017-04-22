<?php

use BetaKiller\Content\ContentElementInterface;

trait Assets_Provider_ContentTrait
{
    use \BetaKiller\Helper\ContentTrait;


    /**
     * Custom upload processing
     *
     * @param ContentElementInterface $model
     * @param array                   $_post_data
     */
    protected function upload_preprocessor($model, array $_post_data)
    {
        $entity_id = (int) Arr::get($_post_data, 'entityID') ?: NULL;
        $entity_item_id = (int) Arr::get($_post_data, 'entityItemID') ?: NULL;

        if ($entity_id)
        {
            $entity = $this->model_factory_content_entity($entity_id);
            $model->set_entity($entity);
        }

        if ($entity_item_id)
        {
            $model->set_entity_item_id($entity_item_id);
        }
    }

    /**
     * Returns concrete storage for current provider
     *
     * @return \Assets_Storage
     */
    protected function storage_factory()
    {
        // TODO move MultiSite dependency to Assets_Storage_Local config
        $assets_path = MultiSite::instance()->getSitePath().DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR;

        /** @var \Assets_Storage_Local $storage */
        $storage = Assets_Storage_Factory::instance()->create('Local');

        return $storage->set_base_path($assets_path.$this->get_storage_path_name());
    }

    abstract protected function get_storage_path_name();
}
