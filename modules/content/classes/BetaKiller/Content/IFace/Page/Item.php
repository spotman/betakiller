<?php

namespace BetaKiller\Content\IFace\Page;

use BetaKiller\Content\IFace\ContentItemBase;

class Item extends ContentItemBase
{
    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function get_data()
    {
        return [
            'page' => $this->get_content_data(),
        ];
    }

    /**
     * @return \Model_ORM_ContentBase
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
