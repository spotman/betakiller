<?php
namespace BetaKiller\IFace\Admin\Content;

use BetaKiller\Helper\ContentUrlContainerHelper;
use BetaKiller\Helper\IFaceHelper;
use BetaKiller\IFace\CrudlsActionsInterface;

class PostItem extends AbstractAdminBase
{
    /**
     * @var \BetaKiller\Helper\ContentUrlContainerHelper
     */
    private $urlParametersHelper;

    /**
     * @Inject
     * @var \BetaKiller\Model\UserInterface
     */
    private $user;

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
     * @var \BetaKiller\Content\Shortcode\ShortcodeFacade
     * @Inject
     */
    private $customTagFacade;

    /**
     * PostItem constructor.
     *
     * @param \BetaKiller\Helper\IFaceHelper               $ifaceHelper
     * @param \BetaKiller\Helper\ContentUrlContainerHelper $urlParametersHelper
     */
    public function __construct(IFaceHelper $ifaceHelper, ContentUrlContainerHelper $urlParametersHelper)
    {
        parent::__construct($ifaceHelper);

        $this->urlParametersHelper = $urlParametersHelper;
    }

    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     * @throws \HTTP_Exception_404
     */
    public function getData(): array
    {
        $post = $this->urlParametersHelper->getContentPost();

        if (!$post) {
            throw new \HTTP_Exception_404();
        }

        $thumbnails = [];

        foreach ($post->getThumbnails() as $thumb) {
            $thumbnails[$thumb->getID()] = $this->assetsHelper->getAttributesForImgTag($thumb, $thumb::SIZE_PREVIEW);
        }

        // Edit latest revision data
        $post->useLatestRevision();

        $status = $post->getCurrentStatus();

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
                    'id'          => $status->getID(),
                    'codename'    => $status->getCodename(),
                    'transitions' => $status->getAllowedTargetTransitionsCodenameArray($this->user),
                ],

                'thumbnails' => $thumbnails,
            ],

            'shortcodes' => $this->customTagFacade->getEditableTagsNames(),
        ];
    }
}
