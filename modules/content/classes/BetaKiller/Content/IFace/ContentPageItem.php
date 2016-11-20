<?php
namespace BetaKiller\Content\IFace;

class ContentPageItem extends ContentPostBase
{
    /**
     * @return \Model_ContentItem
     */
    protected function content_model_factory()
    {
        return $this->model_factory_content_page();
    }

    /**
     * @return string
     */
    protected function get_content_model_url_key()
    {
        return \Model_ContentPage::URL_PARAM;
    }
}
