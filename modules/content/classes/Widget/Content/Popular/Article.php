<?php

use BetaKiller\IFace\Widget;

class Widget_Content_Popular_Article extends Widget
{
    use \BetaKiller\Helper\ContentTrait;

    /**
     * Returns data for View rendering
     *
     * @return array
     */
    public function get_data()
    {
        $orm = $this->model_factory_content_post();

        $limit = (int) $this->getContextParam('limit', 5);

        $current_article = $this->url_parameter_content_post();

        $exclude_id = $current_article ? $current_article->get_id() : NULL;

        /** @var Model_ContentPost[] $articles */
        $articles = $orm->get_popular_articles($limit, $exclude_id);

        $data = [];

        foreach ($articles as $article)
        {
            /** @var \Model_ContentImageElement $thumbnail */
            $thumbnail = $article->get_first_thumbnail();

            $data[] = [
                'label'     =>  $article->get_label(),
                'thumbnail' =>  $thumbnail->get_arguments_for_img_tag($thumbnail::SIZE_PREVIEW),
                'url'       =>  $article->get_public_url(),
                'date'      =>  $article->get_created_at()->format('d.m.Y'),
            ];
        }

        return [
            'articles'  =>  $data,
        ];
    }
}
