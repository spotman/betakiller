<?php
namespace BetaKiller\Helper;

trait ContentTrait
{
    protected function service_content_from_mime($mime)
    {
        return \Service_Content_WithAssets::service_instance_by_mime($mime);
    }

    /**
     * @return \Service_Content_Image
     */
    protected function service_admin_image()
    {
        return \Service_Content_Image::instance();
    }

    /**
     * @return \Service_Content_Attachment
     */
    protected function service_content_attachment()
    {
        return \Service_Content_Attachment::instance();
    }

    /**
     * @return \Service_Content_Youtube
     */
    protected function service_content_youtube()
    {
        return \Service_Content_Youtube::instance();
    }

    /**
     * @param int|null $id
     *
     * @return \Model_ContentItem
     */
    public function model_factory_content_item($id = null)
    {
        return \ORM::factory('ContentItem', $id);
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
     * @return \Model_ContentItem
     */
    public function url_parameter_content_item()
    {
        return $this->url_parameters()->get(\Model_ContentItem::URL_PARAM);
    }

    /**
     * @return \Model_ContentCategory
     */
    public function url_parameter_content_category()
    {
        return $this->url_parameters()->get(\Model_ContentCategory::URL_PARAM);
    }
}
