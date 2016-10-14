<?php
namespace BetaKiller\Helper;

trait Admin
{
    protected function service_admin_content_from_mime($mime)
    {
        return \Service_Admin_ContentWithAssets::service_instance_by_mime($mime);
    }

    /**
     * @return \Service_Admin_Image
     */
    protected function service_admin_image()
    {
        return \Service_Admin_Image::instance();
    }

    /**
     * @return \Service_Admin_Attachment
     */
    protected function service_admin_attachment()
    {
        return \Service_Admin_Attachment::instance();
    }

    /**
     * @return \Service_Admin_Youtube
     */
    protected function service_admin_youtube()
    {
        return \Service_Admin_Youtube::instance();
    }

    /**
     * @param int|null $id
     * @return \Model_AdminContentEntity
     */
    protected function model_factory_admin_content_entity($id = NULL)
    {
        return \ORM::factory('AdminContentEntity', $id);
    }

    /**
     * @param int|null $id
     * @return \Model_AdminImageFile
     */
    protected function model_factory_admin_image_file($id = NULL)
    {
        return \ORM::factory('AdminImageFile', $id);
    }

    /**
     * @param int|null $id
     * @return \Model_AdminAttachmentFile
     */
    protected function model_factory_admin_attachment_file($id = NULL)
    {
        return \ORM::factory('AdminAttachmentFile', $id);
    }

    /**
     * @param int|null $id
     * @return \Model_AdminYoutubeRecord
     */
    protected function model_factory_admin_youtube_record($id = NULL)
    {
        return \ORM::factory('AdminYoutubeRecord', $id);
    }

    /**
     * @return \CustomTag
     */
    protected function custom_tag_instance()
    {
        return \CustomTag::instance();
    }
}
