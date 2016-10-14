<?php defined('SYSPATH') OR die('No direct script access.');

class Service_Admin_Attachment extends Service_Admin_ContentWithAssets
{
    /**
     * Custom HTML-tag name
     *
     * @return string
     */
    public function get_html_custom_tag_name()
    {
        // TODO Move to const on CustomTag
        return 'admin_attachment';
    }

    protected function file_model_factory()
    {
        return $this->model_factory_admin_attachment_file();
    }

    /**
     * @return Assets_Provider_AdminAttachment
     */
    protected function get_assets_provider()
    {
        return \Assets_Provider_Factory::instance()->create('AdminAttachment');
    }
}
