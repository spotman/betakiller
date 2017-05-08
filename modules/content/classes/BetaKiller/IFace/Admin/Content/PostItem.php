<?php
namespace BetaKiller\IFace\Admin\Content;

use BetaKiller\Helper\ContentUrlParametersHelper;

class PostItem extends AdminBase
{
    /**
     * @var \BetaKiller\Helper\ContentUrlParametersHelper
     */
    private $urlParametersHelper;

    /**
     * PostItem constructor.
     *
     * @param \BetaKiller\Helper\ContentUrlParametersHelper $urlParametersHelper
     */
    public function __construct(ContentUrlParametersHelper $urlParametersHelper)
    {
        $this->urlParametersHelper = $urlParametersHelper;
    }

    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function getData()
    {
        $post = $this->urlParametersHelper->getContentPost();

//        $thumbnails = [];
//
//        foreach ($article->getThumbnails() as $thumb)
//        {
//            $thumbnails[] = $thumb->getAttributesForImgTag();
//        }

        $rules = [];

        foreach (\CustomTag::instance()->getAllowedTags() as $tag)
        {
            $rules[$tag] = $tag.'[id,class,align,alt,title,width,height]';
        }

        $status = $post->get_current_status();

        return [
            'post' => [
                'id'            =>  $post->get_id(),
                'uri'           =>  $post->getUri(),
                'label'         =>  $post->getLabel(),
                'content'       =>  $post->getContent(),
                'title'         =>  $post->getTitle(),
                'description'   =>  $post->getDescription(),

                'status'        =>  [
                    'id'            =>  $status->get_id(),
                    'codename'      =>  $status->get_codename(),
                    'transitions'   =>  $status->get_allowed_target_transitions_codename_array(),
                ],

//                'thumbnails'    =>  $thumbnails,
            ],

            'custom_tags_rules'  =>  $rules,
        ];
    }
}
