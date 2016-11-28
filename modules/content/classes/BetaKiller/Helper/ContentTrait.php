<?php
namespace BetaKiller\Helper;

trait ContentTrait
{
    /**
     * @param $mime
     *
     * @return \Service_Content_WithAssets
     * @deprecated
     */
    protected function service_content_assets($mime)
    {
        return \Service_Content_WithAssets::service_instance_by_mime($mime);
    }

    /**
     * @return \Service_ContentFacade
     */
    protected function service_content_facade()
    {
        return \Service_ContentFacade::instance();
    }

    /**
     * @return \Service_Content_Youtube
     */
    protected function service_content_youtube()
    {
        return \Service_Content_Youtube::instance();
    }

    /**
     * @return \Assets_Provider_ContentImage
     */
    protected function assets_provider_content_image()
    {
        return \Assets_Provider_Factory::instance()->create('ContentImage');
    }

    /**
     * @return \Assets_Provider_ContentAttachment
     */
    protected function assets_provider_content_attachment()
    {
        return \Assets_Provider_Factory::instance()->create('ContentAttachment');
    }

    /**
     * @return \Assets_Provider_ContentPostThumbnail
     */
    protected function assets_provider_content_post_thumbnail()
    {
        return \Assets_Provider_Factory::instance()->create('ContentPostThumbnail');
    }

    /**
     * @param int|null $id
     *
     * @return \Model_ContentPost
     */
    public function model_factory_content_post($id = null)
    {
        return \ORM::factory('ContentPost', $id);
    }

    /**
     * @param int|null $id
     * @return \Model_ContentCategory
     */
    public function model_factory_content_category($id = null)
    {
        return \ORM::factory('ContentCategory', $id);
    }

    /**
     * @param int|null $id
     * @return \Model_ContentEntity
     */
    protected function model_factory_content_entity($id = NULL)
    {
        return \ORM::factory('ContentEntity', $id);
    }

    /**
     * @param int|null $id
     * @return \Model_ContentImageElement
     */
    protected function model_factory_content_image_element($id = NULL)
    {
        return \ORM::factory('ContentImageElement', $id);
    }

    /**
     * @param int|null $id
     * @return \Model_ContentPostThumbnail
     */
    protected function model_factory_content_post_thumbnail($id = NULL)
    {
        return \ORM::factory('ContentPostThumbnail', $id);
    }

    /**
     * @param int|null $id
     * @return \Model_ContentAttachmentElement
     */
    protected function model_factory_content_attachment_element($id = NULL)
    {
        return \ORM::factory('ContentAttachmentElement', $id);
    }

    /**
     * @param int|null $id
     * @return \Model_ContentYoutubeRecord
     */
    protected function model_factory_content_youtube_record($id = NULL)
    {
        return \ORM::factory('ContentYoutubeRecord', $id);
    }

    /**
     * @return \CustomTag
     */
    protected function custom_tag_instance()
    {
        return \CustomTag::instance();
    }

    /**
     * @return \Model_ContentPost|NULL
     */
    public function url_parameter_content_post()
    {
        return $this->url_parameters()->get(\Model_ContentPost::URL_PARAM);
    }

    /**
     * @return \Model_ContentCategory
     */
    public function url_parameter_content_category()
    {
        return $this->url_parameters()->get(\Model_ContentCategory::URL_PARAM);
    }
}
