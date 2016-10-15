<?php

use BetaKiller\IFace\Widget;

class Widget_Article_Popular extends Widget
{
    use \BetaKiller\Helper\Article;

    /**
     * Returns data for View rendering
     *
     * @return array
     */
    public function get_data()
    {
        $orm = $this->model_factory_article();

        $limit = $this->getContextParam('limit', 5);

        $articles = $orm->get_popular_articles($limit);

        $data = [];

        foreach ($articles as $article)
        {
            /** @var Model_AdminImageFile $thumbnail */
            $thumbnail = $article->get_first_thumbnail();

            $data[] = [
                'title'     =>  $article->get_title(),
                'thumbnail' =>  $thumbnail->get_img_tag_arguments_with_srcset(),
                'url'       =>  $article->get_public_url(),
            ];
        }

        return [
            'articles'  =>  $data,
        ];
    }
}
