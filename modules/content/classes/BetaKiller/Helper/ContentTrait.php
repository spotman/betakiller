<?php
namespace BetaKiller\Helper;

trait ContentTrait
{
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
     * @return \Assets_Provider_ContentImage|\Assets_Provider_Image
     */
    protected function assets_provider_content_image()
    {
        return \Assets_Provider_Factory::instance()->create('ContentImage');
    }

    /**
     * @return \Assets_Provider_ContentAttachment|\Assets_Provider
     */
    protected function assets_provider_content_attachment()
    {
        return \Assets_Provider_Factory::instance()->create('ContentAttachment');
    }

    /**
     * @return \Assets_Provider_ContentPostThumbnail|\Assets_Provider_Image
     */
    protected function assets_provider_content_post_thumbnail()
    {
        return \Assets_Provider_Factory::instance()->create('ContentPostThumbnail');
    }

    /**
     * @param int|null $id
     *
     * @return \Model_ContentPost|\ORM
     */
    public function model_factory_content_post($id = null)
    {
        return \ORM::factory('ContentPost', $id);
    }

    /**
     * @param int|null $id
     * @return \Model_ContentCategory|\ORM
     */
    public function model_factory_content_category($id = null)
    {
        return \ORM::factory('ContentCategory', $id);
    }

    /**
     * @param int|null $id
     * @return \Model_ContentEntity|\ORM
     */
    protected function model_factory_content_entity($id = NULL)
    {
        return \ORM::factory('ContentEntity', $id);
    }

    /**
     * @param int|null $id
     * @return \Model_ContentImageElement|\ORM
     */
    protected function model_factory_content_image_element($id = NULL)
    {
        return \ORM::factory('ContentImageElement', $id);
    }

    /**
     * @param int|null $id
     * @return \Model_ContentPostThumbnail|\ORM
     */
    protected function model_factory_content_post_thumbnail($id = NULL)
    {
        return \ORM::factory('ContentPostThumbnail', $id);
    }

    /**
     * @param int|null $id
     * @return \Model_ContentAttachmentElement|\ORM
     */
    protected function model_factory_content_attachment_element($id = NULL)
    {
        return \ORM::factory('ContentAttachmentElement', $id);
    }

    /**
     * @param int|null $id
     * @return \Model_ContentYoutubeRecord|\ORM
     */
    protected function model_factory_content_youtube_record($id = NULL)
    {
        return \ORM::factory('ContentYoutubeRecord', $id);
    }

    /**
     * @param int|null $id
     * @return \Model_Quote|\ORM
     */
    protected function model_factory_quote($id = NULL)
    {
        return \ORM::factory('Quote', $id);
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
