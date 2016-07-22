<?php defined('SYSPATH') OR die('No direct script access.');

class Service_Admin_Image extends Service_Admin_Content
{
    /**
     * Имя кастомного HTML-тега для вставки контента
     *
     * @return string
     */
    public function get_html_custom_tag_name()
    {
        return 'admin_image';
    }

    protected function file_model_factory()
    {
        return $this->model_factory_admin_image_file();
    }

    /**
     * @return Assets_Provider_AdminImage
     */
    protected function get_assets_provider()
    {
        return \Assets_Provider_Factory::instance()->create('AdminImage');
    }
}
