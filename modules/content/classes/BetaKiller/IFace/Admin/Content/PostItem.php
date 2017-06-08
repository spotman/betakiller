<?php
namespace BetaKiller\IFace\Admin\Content;

use BetaKiller\Helper\ContentUrlParametersHelper;

class PostItem extends AbstractAdminBase
{
    /**
     * @var \BetaKiller\Helper\ContentUrlParametersHelper
     */
    private $urlParametersHelper;

    /**
     * @Inject
     * TODO move to constructor
     * @var \BetaKiller\Helper\AclHelper
     */
    private $aclHelper;

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
    public function getData(): array
    {
        $post = $this->urlParametersHelper->getContentPost();

        /** @var \BetaKiller\Acl\Resource\ContentPostResource $contentPostResource */
        $contentPostResource = $this->aclHelper->getEntityAclResource($post);

        $thumbnails = [];

        foreach ($post->getThumbnails() as $thumb) {
            $thumbnails[$thumb->get_id()] = $thumb->getAttributesForImgTag($thumb::SIZE_PREVIEW);
        }

        // Edit latest revision data
        $post->useLatestRevision();

        $status = $post->get_current_status();

        return [
            'post' => [
                'id'          => $post->getID(),
                'uri'         => $post->getUri(),
                'label'       => $post->getLabel(),
                'content'     => $post->getContent(),
                'title'       => $post->getTitle(),
                'description' => $post->getDescription(),

                'needsCategory'   => $post->needsCategory(),
                'isUpdateAllowed' => $contentPostResource->isUpdateAllowed(),

                'status' => [
                    'id'          => $status->get_id(),
                    'codename'    => $status->get_codename(),
                    'transitions' => $status->get_allowed_target_transitions_codename_array(),
                ],

                'thumbnails' => $thumbnails,
            ],

            'custom_tags' => \CustomTag::instance()->getAllowedTags(),
        ];
    }
}
