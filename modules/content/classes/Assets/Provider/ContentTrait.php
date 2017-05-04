<?php

use BetaKiller\Assets\AssetsStorageFactory;
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
    protected function uploadPreprocessor($model, array $_post_data)
    {
        $entity_id = (int) Arr::get($_post_data, 'entityID') ?: NULL;
        $entity_item_id = (int) Arr::get($_post_data, 'entityItemID') ?: NULL;

        if ($entity_id) {
            $entity = $this->model_factory_content_entity($entity_id);
            $model->set_entity($entity);
        }

        if ($entity_item_id) {
            $model->set_entity_item_id($entity_item_id);
        }
    }

    /**
     * Returns concrete storage for current provider
     *
     * @return \BetaKiller\Assets\Storage\AbstractAssetsStorage
     */
    protected function createStorage()
    {
        // TODO move MultiSite dependency to AssetsStorageLocal config
        $assets_path = MultiSite::instance()->getSitePath().DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR;

        /** @var \BetaKiller\Assets\Storage\AssetsStorageLocal $storage */
        $storage = AssetsStorageFactory::instance()->create('Local');

        return $storage->setBasePath($assets_path.$this->getStoragePathName());
    }

    abstract protected function getStoragePathName();
}
