<?php
namespace BetaKiller\IFace\Admin\Content;

use BetaKiller\Exception\NotFoundHttpException;
use BetaKiller\Helper\AclHelper;
use BetaKiller\Helper\AssetsHelper;
use BetaKiller\Helper\ContentUrlContainerHelper;
use BetaKiller\IFace\CrudlsActionsInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\EntityRepository;
use BetaKiller\Repository\ShortcodeRepository;

class PostItem extends AbstractAdminBase
{
    /**
     * @var \BetaKiller\Helper\ContentUrlContainerHelper
     */
    private $urlParametersHelper;

    /**
     * @var \BetaKiller\Model\UserInterface
     */
    private $user;

    /**
     * @var \BetaKiller\Helper\AclHelper
     */
    private $aclHelper;

    /**
     * @var \BetaKiller\Helper\AssetsHelper
     */
    private $assetsHelper;

    /**
     * @var \BetaKiller\Repository\ShortcodeRepository
     */
    private $shortcodeRepo;

    /**
     * @var \BetaKiller\Repository\EntityRepository
     */
    private $entityRepo;

    /**
     * PostItem constructor.
     *
     * @param \BetaKiller\Helper\ContentUrlContainerHelper $urlParametersHelper
     * @param \BetaKiller\Model\UserInterface              $user
     * @param \BetaKiller\Helper\AclHelper                 $aclHelper
     * @param \BetaKiller\Helper\AssetsHelper              $assetsHelper
     * @param \BetaKiller\Repository\ShortcodeRepository   $shortcodeRepo
     * @param \BetaKiller\Repository\EntityRepository      $entityRepo
     */
    public function __construct(
        ContentUrlContainerHelper $urlParametersHelper,
        UserInterface $user,
        AclHelper $aclHelper,
        AssetsHelper $assetsHelper,
        ShortcodeRepository $shortcodeRepo,
        EntityRepository $entityRepo
    ) {
        parent::__construct();

        $this->urlParametersHelper = $urlParametersHelper;
        $this->user                = $user;
        $this->aclHelper           = $aclHelper;
        $this->assetsHelper        = $assetsHelper;
        $this->shortcodeRepo       = $shortcodeRepo;
        $this->entityRepo          = $entityRepo;
    }

    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     * @throws \BetaKiller\Exception\NotFoundHttpException
     */
    public function getData(): array
    {
        $post = $this->urlParametersHelper->getContentPost();

        if (!$post) {
            throw new NotFoundHttpException();
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

            'shortcodes' => $this->shortcodeRepo->getEditableTagsNames(),
        ];
    }
}
