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
    public function get_data()
    {
        $post = $this->url_parameter_content_post();


//        $thumbnails = [];
//
//        foreach ($article->get_thumbnails() as $thumb)
//        {
//            $thumbnails[] = $thumb->get_attributes_for_img_tag();
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
                'title'         =>  $post->get_title(),
                'description'   =>  $post->get_description(),

                'transitions'   =>  $post->get_allowed_target_transitions_codenames(),

//                'thumbnails'    =>  $thumbnails,
            ],

            'custom_tags_rules'  =>  $rules,
        ];
    }
}
