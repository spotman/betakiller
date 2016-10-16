<?php

class Service_Content_Youtube extends Service_Content
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

    public function get_youtube_id_from_embed_url($url)
    {
        $path = parse_url($url, PHP_URL_PATH);

        if ( strpos($path, 'embed') === null)
            throw new Service_Exception('No embed in URL :url', [':url' => $url]);

        return basename($path);
    }

    /**
     * @param string $youtube_id
     * @return Model_ContentYoutubeRecord
     */
    public function find_record_by_youtube_id($youtube_id)
    {
        $model = $this->file_model_factory()->find_by_youtube_id($youtube_id);

        if (!$model)
        {
            $model = $this->file_model_factory()->set_youtube_id($youtube_id);
        }

        return $model;
    }

    /**
     * @return Model_ContentYoutubeRecord
     */
    protected function file_model_factory()
    {
        return $this->model_factory_content_youtube_record();
    }

}
