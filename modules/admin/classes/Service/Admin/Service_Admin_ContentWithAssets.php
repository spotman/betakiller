<?php

/**
 * Created by PhpStorm.
 * User: spotman
 * Date: 14.10.16
 * Time: 21:09
 */
abstract class Service_Admin_ContentWithAssets extends Service_Admin_Content
{
    /**
     * @param $mime
     * @return Service_Admin_ContentWithAssets
     */
    public static function service_instance_by_mime($mime)
    {
        /** @var Service_Admin_ContentWithAssets[] $mime_services */
        $mime_services = [
            Service_Admin_Image::instance(),
        ];

        foreach ($mime_services as $service)
        {
            $allowed_mimes = $service->get_assets_provider()->get_allowed_mime_types();

            if ($allowed_mimes AND is_array($allowed_mimes) AND in_array($mime, $allowed_mimes))
                return $service;
        }

        // Default way
        return Service_Admin_Attachment::instance();
    }

    /**
     * @param $full_path
     * @param $original_name
     * @param Model_AdminContentEntity $entity
     * @param null $entity_item_id
     * @return Model_AdminContentFile
     */
    public function store_file($full_path, $original_name, Model_AdminContentEntity $entity, $entity_item_id = NULL)
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
     * @return Assets_Provider_AdminImage|Assets_Provider_AdminAttachment
     */
    abstract protected function get_assets_provider();
}
