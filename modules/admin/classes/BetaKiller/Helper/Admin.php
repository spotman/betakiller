<?php
namespace BetaKiller\Helper;

trait Admin
{
    protected function service_admin_content_from_mime($mime)
    {
        return \Service_Admin_Content::service_instance_by_mime($mime);
    }

    /**
     * @return \Service_Admin_Content
     */
    protected function service_admin_image()
    {
        return \Service_Admin_Image::instance();
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
     * @return \CustomTag
     */
    protected function custom_tag_instance()
    {
        return \CustomTag::instance();
    }
}
