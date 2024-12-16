<?php

namespace BetaKiller\IFace\Admin\Content;

use BetaKiller\Acl\EntityPermissionResolverInterface;
use BetaKiller\Acl\Resource\ContentPostResource;
use BetaKiller\Exception\NotFoundHttpException;
use BetaKiller\Helper\AssetsHelper;
use BetaKiller\Helper\ContentUrlContainerHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Repository\EntityRepository;
use BetaKiller\Repository\ShortcodeRepository;
use Psr\Http\Message\ServerRequestInterface;

final readonly class PostItemIFace extends AbstractContentAdminIFace
{
    /**
     * PostItem constructor.
     *
     * @param \BetaKiller\Acl\EntityPermissionResolverInterface $entityPermissionResolver
     * @param \BetaKiller\Helper\AssetsHelper                   $assetsHelper
     * @param \BetaKiller\Repository\ShortcodeRepository        $shortcodeRepo
     * @param \BetaKiller\Repository\EntityRepository           $entityRepo
     */
    public function __construct(
        private EntityPermissionResolverInterface $entityPermissionResolver,
        private AssetsHelper $assetsHelper,
        private ShortcodeRepository $shortcodeRepo,
        private EntityRepository $entityRepo
    ) {
    }

    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     * @throws \BetaKiller\Assets\Exception\AssetsException
     * @throws \BetaKiller\Assets\Exception\AssetsStorageException
     * @throws \BetaKiller\Exception\NotFoundHttpException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \Spotman\Acl\AclException
     */
    public function getData(ServerRequestInterface $request): array
    {
        $user = ServerRequestHelper::getUser($request);
        $post = ContentUrlContainerHelper::getContentPost($request);

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

        $status = $post->getWorkflowState();

        $updateAllowed = $this->entityPermissionResolver->isAllowed($user, $post, ContentPostResource::ACTION_UPDATE);

        return [
            'post' => [
                'id'          => $post->getID(),
                'uri'         => $post->getUri(),
                'label'       => $post->getLabel(),
                'content'     => $post->getContent(),
                'title'       => $post->getTitle(),
                'description' => $post->getDescription(),

                'needsCategory'     => $post->needsCategory(),
                'is_update_allowed' => $updateAllowed,

                'status' => [
                    'id'          => $status->getID(),
                    'codename'    => $status->getCodename(),
                    'transitions' => [],
                    // TODO Replace with list of "isTransitionAllowed" markers
//                    'transitions' => $status->getAllowedTargetTransitionsCodenameArray($user),
                ],

                'thumbnails' => $thumbnails,
            ],

            'entity_slug' => $entity->getSlug(),

            'shortcodes' => $this->shortcodeRepo->getEditableTagsNames(),
        ];
    }
}
