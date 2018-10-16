<?php
namespace BetaKiller\IFace\Admin\Content;

use BetaKiller\CrudlsActionsInterface;
use BetaKiller\Exception\NotFoundHttpException;
use BetaKiller\Helper\AclHelper;
use BetaKiller\Helper\AssetsHelper;
use BetaKiller\Helper\ContentUrlContainerHelper;
use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\EntityRepository;
use BetaKiller\Repository\ShortcodeRepository;
use Psr\Http\Message\ServerRequestInterface;

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
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     * @throws \BetaKiller\Assets\Exception\AssetsException
     * @throws \BetaKiller\Assets\AssetsStorageException
     * @throws \BetaKiller\Exception\NotFoundHttpException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \Kohana_Exception
     * @throws \Spotman\Acl\Exception
     */
    public function getData(ServerRequestInterface $request): array
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

        $updateAllowed = $this->aclHelper->isEntityActionAllowed($this->user, $post,
            CrudlsActionsInterface::ACTION_UPDATE);

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
