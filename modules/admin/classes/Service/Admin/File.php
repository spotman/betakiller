<?php defined('SYSPATH') OR die('No direct script access.');

class Service_Admin_Attachment extends Service_Admin_Content
{
    /**
     * Имя кастомного HTML-тега для вставки контента
     *
     * @return string
     */
    public function get_html_custom_tag_name()
    {
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
