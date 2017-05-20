<?php
namespace BetaKiller\IFace\Admin\Content;

use BetaKiller\Helper\ContentUrlParametersHelper;
use BetaKiller\Acl\Resource\ContentPostResource;
use Spotman\Acl\AccessResolver\AclAccessResolverInterface;

class PostItem extends AdminBase
{
    /**
     * @var \BetaKiller\Acl\Resource\ContentPostResource
     */
    private $contentPostResource;

    /**
     * @var \BetaKiller\Helper\ContentUrlParametersHelper
     */
    private $urlParametersHelper;

    /**
     * PostItem constructor.
     *
     * @param \BetaKiller\Helper\ContentUrlParametersHelper $urlParametersHelper
     */

    /**
     * PostItem constructor.
     *
     * @param \BetaKiller\Helper\ContentUrlParametersHelper          $urlParametersHelper
     * @param \BetaKiller\Acl\Resource\ContentPostResource           $resource
     * @param \Spotman\Acl\AccessResolver\AclAccessResolverInterface $resolver
     */
    public function __construct(
        ContentUrlParametersHelper $urlParametersHelper,
        ContentPostResource $resource,
        AclAccessResolverInterface $resolver
    )
    {
        $this->urlParametersHelper = $urlParametersHelper;
        $this->contentPostResource = $resource->useResolver($resolver);
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

        $thumbnails = [];

        foreach ($post->getThumbnails() as $thumb)
        {
            $thumbnails[$thumb->get_id()] = $thumb->getAttributesForImgTag($thumb::SIZE_PREVIEW);
        }

        // Edit latest revision data
        $post->useLatestRevision();

        $status = $post->get_current_status();

        $this->contentPostResource->useStatusRelatedModel($post);

        return [
            'post' => [
                'id'            =>  $post->get_id(),
                'uri'           =>  $post->getUri(),
                'label'         =>  $post->getLabel(),
                'content'       =>  $post->getContent(),
                'title'         =>  $post->getTitle(),
                'description'   =>  $post->getDescription(),

                'needsCategory'   => $post->needsCategory(),
                'isUpdateAllowed' => $this->contentPostResource->isUpdateAllowed(),

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
