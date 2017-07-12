<?php
namespace BetaKiller\IFace\Admin\Content;

use BetaKiller\Helper\ContentUrlContainerHelper;
use BetaKiller\IFace\CrudlsActionsInterface;

class PostItem extends AbstractAdminBase
{
    /**
     * @var \BetaKiller\Helper\ContentUrlContainerHelper
     */
    private $urlParametersHelper;

    /**
     * @Inject
     * TODO move to constructor
     * @var \BetaKiller\Helper\AclHelper
     */
    private $aclHelper;

    /**
     * @var \BetaKiller\Helper\AssetsHelper
     * @Inject
     */
    private $assetsHelper;

    /**
     * @var \CustomTagFacade
     * @Inject
     */
    private $customTagFacade;

    /**
     * PostItem constructor.
     *
     * @param \BetaKiller\Helper\ContentUrlContainerHelper $urlParametersHelper
     */
    public function __construct(ContentUrlContainerHelper $urlParametersHelper)
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

        $thumbnails = [];

        foreach ($post->getThumbnails() as $thumb) {
            $thumbnails[$thumb->getID()] = $this->assetsHelper->getAttributesForImgTag($thumb, $thumb::SIZE_PREVIEW);
        }

        // Edit latest revision data
        $post->useLatestRevision();

        $status = $post->get_current_status();

        $updateAllowed = $this->aclHelper->isEntityActionAllowed($post, CrudlsActionsInterface::ACTION_UPDATE);

        return [
            'post' => [
                'id'          => $post->getID(),
                'uri'         => $post->getUri(),
                'label'       => $post->getLabel(),
                'content'     => $post->getContent(),
                'title'       => $post->getTitle(),
                'description' => $post->getDescription(),

                'needsCategory'   => $post->needsCategory(),
                'isUpdateAllowed' => $updateAllowed,

                'status' => [
                    'id'          => $status->get_id(),
                    'codename'    => $status->get_codename(),
                    'transitions' => $status->get_allowed_target_transitions_codename_array(),
                ],

                'thumbnails' => $thumbnails,
            ],

            'custom_tags' => $this->customTagFacade->getAllowedTags(),
        ];
    }
}
