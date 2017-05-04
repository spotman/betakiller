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
        return CustomTag::PHOTO;
    }

    /**
     * @return Assets_Provider_ContentImage
     */
    protected function get_assets_provider()
    {
        return \BetaKiller\Assets\AssetsProviderFactory::instance()->create('ContentImage');
    }
}
