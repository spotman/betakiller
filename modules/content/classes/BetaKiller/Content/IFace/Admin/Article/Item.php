<?php
namespace BetaKiller\Content\IFace\Admin\Article;

use Model_ContentArticle;
use BetaKiller\Content\IFace\Admin;

class Item extends Admin
{
    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function get_data()
    {
        /** @var Model_ContentArticle $article */
        $article = $this->url_parameters()->get('Article');

//        $thumbnails = [];
//
//        foreach ($article->get_thumbnails() as $thumb)
//        {
//            $thumbnails[] = $thumb->get_arguments_for_img_tag();
//        }

        return [
            'article' => [
                'id'          => $article->get_id(),
                'uri'         => $article->get_uri(),
                'label'       => $article->get_label(),
                'content'     => $article->get_content(),
                'title'       => $article->get_title(),
                'description' => $article->get_description(),

//                'thumbnails'    =>  $thumbnails,
            ],
        ];
    }
}
