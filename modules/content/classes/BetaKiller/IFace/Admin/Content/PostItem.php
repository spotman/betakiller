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
     * @param \BetaKiller\Helper\ContentUrlParametersHelper          $urlParametersHelper
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

        /** @var \BetaKiller\Acl\Resource\ContentPostResource $contentPostResource */
        $contentPostResource = $this->acl->getResource('ContentPost');

        $contentPostResource->useStatusRelatedModel($post);

        $thumbnails = [];

        foreach ($post->getThumbnails() as $thumb) {
            $thumbnails[$thumb->get_id()] = $thumb->getAttributesForImgTag($thumb::SIZE_PREVIEW);
        }

        // Edit latest revision data
        $post->useLatestRevision();

        $status = $post->get_current_status();

        return [
            'post' => [
                'id'            =>  $post->get_id(),
                'uri'           =>  $post->getUri(),
                'label'         =>  $post->getLabel(),
                'content'       =>  $post->getContent(),
                'title'         =>  $post->getTitle(),
                'description'   =>  $post->getDescription(),

                'needsCategory'   => $post->needsCategory(),
                'isUpdateAllowed' => $contentPostResource->isUpdateAllowed(),

                'status'        =>  [
                    'id'            =>  $status->get_id(),
                    'codename'      =>  $status->get_codename(),
                    'transitions'   =>  $status->get_allowed_target_transitions_codename_array(),
                ],

                'thumbnails'    =>  $thumbnails,
            ],

            'custom_tags'  =>  \CustomTag::instance()->getAllowedTags(),
        ];
    }
}
