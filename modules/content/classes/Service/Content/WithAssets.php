<?php

abstract class Service_Content_WithAssets extends Service_Content
{
    /**
     * @param string $mime
     * @return Service_Content_WithAssets
     */
    public static function service_instance_by_mime($mime)
    {
        /** @var Service_Content_WithAssets[] $mime_services */
        $mime_services = [
            Service_Content_Image::instance(),
        ];

        foreach ($mime_services as $service)
        {
            $allowed_mimes = $service->get_assets_provider()->get_allowed_mime_types();

            if ($allowed_mimes AND is_array($allowed_mimes) AND in_array($mime, $allowed_mimes))
                return $service;
        }

        // Default way
        return Service_Content_Attachment::instance();
    }

    /**
     * @param $full_path
     * @param $original_name
     * @param Model_ContentEntity $entity
     * @param null $entity_item_id
     * @return Model_ContentElementInterface
     */
    public function store_file($full_path, $original_name, Model_ContentEntity $entity, $entity_item_id = NULL)
    {
        $provider = $this->get_assets_provider();

        $post_data = [
            'entityID'      =>  $entity->get_id(),
            'entityItemID'  =>  $entity_item_id,
        ];

        return $provider->store($full_path, $original_name, $post_data);
    }

    public function get_allowed_mime_types()
    {
        return $this->get_assets_provider()->get_allowed_mime_types();
    }

    /**
     * @return Assets_Provider_ContentImage|Assets_Provider_ContentAttachment
     */
    abstract protected function get_assets_provider();
}
