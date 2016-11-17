<?php

namespace BetaKiller\Content\IFace\Article;

use BetaKiller\Content\IFace\Base;
use DateInterval;
use Model_ContentArticle;

class Item extends Base
{
    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function get_data()
    {
        $article = $this->get_article_model();

        $this
            ->set_last_modified($article->get_last_modified())
            ->set_expires_interval(new DateInterval('P1D'));    // One day

        $user = $this->current_user(TRUE);

        // Count guest views only
        if (!$user) {
            $article->increment_views_count()->save();
        }

        $thumbnails = [];

        foreach ($article->get_thumbnails() as $thumb) {
            $thumbnails[] = $thumb->get_arguments_for_img_tag($thumb::SIZE_ORIGINAL);

            // TODO get image last_modified and set it to iface
        }

        return [
            'article' => [
                'id'            =>  $article->get_id(),
                'label'         =>  $article->get_label(),
                'content'       =>  $article->get_content(),
                'created_at'    =>  $article->get_created_at(),
                'updated_at'    =>  $article->get_updated_at(),
                'thumbnails'    =>  $thumbnails,
            ],
        ];
    }

    protected function get_article_model()
    {
        if ( $this->is_default() )
        {
            $uri = $this->get_index_article_uri();

            /** @var Model_ContentArticle $model */
            $model = $this->model_factory_article()->filter_uri($uri)->find();

            if (!$model->loaded())
                throw new \IFace_Exception('Can not find default article with uri :value', [':value' => $uri]);

            $this->url_parameters()->set($model::URL_PARAM, $model);
        }
        else
        {
            $model = $this->url_parameter_article();
        }

        return $model;
    }

    protected function get_index_article_uri()
    {
        return 'index';
    }
}
