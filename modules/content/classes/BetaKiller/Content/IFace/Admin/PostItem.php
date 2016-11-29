<?php
namespace BetaKiller\Content\IFace\Admin;

use Model_ContentArticle;
use BetaKiller\Content\IFace\Admin;

class PostItem extends Admin
{
    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function get_data()
    {
        $article = $this->url_parameter_content_post();


//        $thumbnails = [];
//
//        foreach ($article->get_thumbnails() as $thumb)
//        {
//            $thumbnails[] = $thumb->get_arguments_for_img_tag();
//        }

        $rules = [];

        foreach (\CustomTag::instance()->get_allowed_tags() as $tag)
        {
            $rules[$tag] = $tag.'[id,class,align,alt,title,width,height]';
        }

        return [
            'post' => [
                'id'          => $article->get_id(),
                'uri'         => $article->get_uri(),
                'label'       => $article->get_label(),
                'content'     => $article->get_content(),
                'title'       => $article->get_title(),
                'description' => $article->get_description(),

//                'thumbnails'    =>  $thumbnails,
            ],

            'custom_tags_rules'  =>  $rules,
        ];
    }
}
