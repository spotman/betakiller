<?php defined('SYSPATH') OR die('No direct script access.');

class Service_Content_Image extends Service_Content_WithAssets
{
    /**
     * Имя кастомного HTML-тега для вставки контента
     *
     * @return string
     */
    public function get_html_custom_tag_name()
    {
        return CustomTag::IMAGE;
    }

    protected function file_model_factory()
    {
        return $this->model_factory_content_image_element();
    }

    /**
     * @return Assets_Provider_ContentImage
     */
    protected function get_assets_provider()
    {
        return \Assets_Provider_Factory::instance()->create('ContentImage');
    }
}
