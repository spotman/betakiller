<?php

namespace BetaKiller\Content\IFace\Article;

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
        /** @var \Model_ContentArticle $model */
        $model = $this->get_content_model();

        $data = $this->get_content_data();

        $thumbnails = [];

        foreach ($model->get_thumbnails() as $thumb) {
            $thumbnails[] = $thumb->get_arguments_for_img_tag($thumb::SIZE_ORIGINAL);

            // TODO get image last_modified and set it to iface
        }

        $data['thumbnails'] = $thumbnails;

        return [
            'article' => $data,
        ];
    }

    /**
     * @return \Model_ORM_ContentBase
     */
    protected function content_model_factory()
    {
        return $this->model_factory_content_article();
    }

    /**
     * @return string
     */
    protected function get_content_model_url_key()
    {
        return \Model_ContentArticle::URL_PARAM;
    }
}
