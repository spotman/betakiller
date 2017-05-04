<?php
namespace BetaKiller\IFace\Admin\Content;

class PostItem extends AdminBase
{
    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function getData()
    {
        $post = $this->url_parameter_content_post();


//        $thumbnails = [];
//
//        foreach ($article->get_thumbnails() as $thumb)
//        {
//            $thumbnails[] = $thumb->getAttributesForImgTag();
//        }

        $rules = [];

        foreach (\CustomTag::instance()->get_allowed_tags() as $tag)
        {
            $rules[$tag] = $tag.'[id,class,align,alt,title,width,height]';
        }

        return [
            'post' => [
                'id'            =>  $post->get_id(),
                'uri'           =>  $post->get_uri(),
                'label'         =>  $post->get_label(),
                'content'       =>  $post->get_content(),
                'title'         =>  $post->getTitle(),
                'description'   =>  $post->getDescription(),

                'transitions'   =>  $post->get_allowed_target_transitions_codenames(),

//                'thumbnails'    =>  $thumbnails,
            ],

            'custom_tags_rules'  =>  $rules,
        ];
    }
}
