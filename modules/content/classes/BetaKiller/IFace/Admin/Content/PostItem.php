<?php
namespace BetaKiller\IFace\Admin\Content;

use BetaKiller\Helper\ContentUrlContainerHelper;
use BetaKiller\IFace\CrudlsActionsInterface;
use BetaKiller\Model\ContentPostInterface;
use BetaKiller\Model\EntityModelInterface;

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
     * @Inject
     * @var \BetaKiller\Repository\EntityRepository
     */
    private $entityRepo;

    /**
     * PostItem constructor.
     *
     * @param \BetaKiller\Helper\ContentUrlContainerHelper $urlParametersHelper
     */
    public function __construct(ContentUrlContainerHelper $urlParametersHelper)
    {
        parent::__construct();

        $this->urlParametersHelper = $urlParametersHelper;
    }

    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     * @throws \BetaKiller\Exception
     * @throws \HTTP_Exception_404
     */
    public function getData(): array
    {
        $post = $this->urlParametersHelper->getContentPost();

        if (!$post) {
            throw new \HTTP_Exception_404();
        }

        $entity = $this->entityRepo->findByEntityInstance($post);

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

            'entity_slug' => $entity->getSlug(),

            'shortcodes' => $this->customTagFacade->getEditableTagsNames(),
        ];
    }
}
