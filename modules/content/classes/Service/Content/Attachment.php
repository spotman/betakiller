<?php defined('SYSPATH') OR die('No direct script access.');

class Service_Content_Attachment extends Service_Content_WithAssets
{
    /**
     * Custom HTML-tag name
     *
     * @return string
     */
    public function get_html_custom_tag_name()
    {
        return CustomTag::ATTACHMENT;
    }

    protected function file_model_factory()
    {
        return $this->model_factory_content_attachment_element();
    }

    /**
     * @return Assets_Provider_ContentAttachment
     */
    protected function get_assets_provider()
    {
        return \Assets_Provider_Factory::instance()->create('AdminAttachment');
    }
}
