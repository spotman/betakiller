<?php

class Service_Admin_Youtube extends Service_Admin_Content
{
    /**
     * Custom HTML-tag name related to current service
     *
     * @return string
     */
    public function get_html_custom_tag_name()
    {
        return CustomTag::YOUTUBE;
    }

    /**
     * @return Model_AdminYoutubeRecord
     */
    protected function file_model_factory()
    {
        return $this->model_factory_admin_youtube_record();
    }

}
