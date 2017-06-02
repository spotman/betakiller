<?php

namespace BetaKiller\Widget\Admin;

use BetaKiller\Acl\Resource\ContentPostResource;
use BetaKiller\Helper\ContentTrait;
use BetaKiller\Helper\ContentUrlParametersHelper;
use BetaKiller\Helper\IFaceHelper;
use BetaKiller\IFace\CrudlsActionsInterface;
use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\IFace\IFaceProvider;
use BetaKiller\IFace\Widget\AbstractAdminWidget;
use BetaKiller\Model\DispatchableEntityInterface;
use BetaKiller\Model\IFaceZone;
use BetaKiller\Model\UserInterface;
use Model_ContentCommentStatus;

class BarWidget extends AbstractAdminWidget
{
    use ContentTrait;

    /**
     * @var \BetaKiller\IFace\Url\UrlDispatcher
     */
    protected $dispatcher;

    /**
     * @var \BetaKiller\Helper\ContentUrlParametersHelper
     */
    private $contentUrlParamHelper;

    /**
     * @var \BetaKiller\IFace\IFaceProvider
     */
    private $ifaceProvider;

    public function __construct(
        UserInterface $user,
        IFaceHelper $ifaceHelper,
        ContentUrlParametersHelper $cUrlParamHelper,
        IFaceProvider $ifaceProvider
    )
    {
        parent::__construct($user);

        $this->ifaceHelper           = $ifaceHelper;
        $this->contentUrlParamHelper = $cUrlParamHelper;
        $this->ifaceProvider         = $ifaceProvider;
    }

    /**
     * Returns data for View rendering
     *
     * @return array
     */
    public function getData(): array
    {
        $isAdminZone = $this->ifaceHelper->getCurrentIFace()->getZoneName() === IFaceZone::ADMIN_ZONE;

        $entity = $this->detectPrimaryEntity();

        $data = [
            'isAdminZone'       => $isAdminZone,
            'enabled'           => true,
            'comments'          => $this->getCommentsData(),
            'createButtonItems' => $this->getCreateButtonItems(),
            'primaryEntity'     => [
                'previewUrl' => $this->getPreviewButtonUrl($entity),
                'publicUrl'  => $this->getPublicReadButtonUrl($entity),
                'adminUrl'   => $this->getAdminEditButtonUrl($entity),
            ],
        ];

        return $data;
    }

    protected function isEmptyResponseAllowed(): bool
    {
        // If user is not authorized, then silently exiting
        return true;
    }

    private function getCreateButtonItems(): array
    {
        $items  = [];
        $ifaces = $this->ifaceProvider->getByActionAndZone(CrudlsActionsInterface::ACTION_CREATE, IFaceZone::ADMIN_ZONE);

        foreach ($ifaces as $iface) {
            if (!$this->aclHelper->isIFaceAllowed($iface)) {
                continue;
            }

            $items[] = [
                'label' => $iface->getLabel(),
                'url'   => $iface->url(),
            ];
        }

        return $items;
    }

    private function getCommentsData(): ?array
    {
        $commentOrm = $this->model_factory_content_comment();
        $statusOrm  = $this->model_factory_content_comment_status();

        $status       = $statusOrm->getPendingStatus();
        $pendingCount = $commentOrm->getCommentsCount($status);

        /** @var \BetaKiller\Acl\Resource\ContentCommentResource $resource */
        $resource = $this->aclHelper->getResource('ContentComment');

        if (!$resource->isStatusActionAllowed($status, $resource::ACTION_UPDATE)) {
            return null;
        }

        $url = $pendingCount
            ? $this->getCommentsListByStatusIfaceUrl($status)
            : $this->getCommentsRootIfaceUrl();

        return [
            'url'   => $url,
            'count' => $pendingCount,
        ];
    }

    private function getCommentsListByStatusIfaceUrl(Model_ContentCommentStatus $status): string
    {
        /** @var \BetaKiller\IFace\Admin\Content\CommentListByStatus $iface */
        $iface = $this->ifaceHelper->createIFaceFromCodename('Admin_Content_CommentListByStatus');

        $param = $this->contentUrlParamHelper->createEmpty();

        $param->setEntity($status);

        return $iface->url($param);
    }

    private function getCommentsRootIfaceUrl(): string
    {
        /** @var \BetaKiller\IFace\Admin\Content\CommentIndex $iface */
        $iface = $this->ifaceHelper->createIFaceFromCodename('Admin_Content_CommentIndex');

        return $iface->url();
    }

    private function getPreviewButtonUrl(?DispatchableEntityInterface $entity): ?string
    {
        if (!$entity) {
            return null;
        }

        $currentIFace = $this->ifaceHelper->getCurrentIFace();
        $currentZone  = $currentIFace->getZoneName();

        // No preview button in public zone
        if ($currentZone === IFaceZone::PUBLIC_ZONE) {
            return null;
        }

        try {
            return $this->ifaceHelper->getEntityUrl($entity, ContentPostResource::ACTION_PREVIEW);
        } catch (IFaceException $e) {
            // No iface found for preview action
            return null;
        }
    }

    private function getAdminEditButtonUrl(?DispatchableEntityInterface $entity): ?string
    {
        return $this->getPrimaryEntityActionUrl($entity, IFaceZone::ADMIN_ZONE);
    }

    private function getPublicReadButtonUrl(?DispatchableEntityInterface $entity): ?string
    {
        return $this->getPrimaryEntityActionUrl($entity, IFaceZone::PUBLIC_ZONE);
    }

    private function getPrimaryEntityActionUrl(?DispatchableEntityInterface $entity, $targetZone): ?string
    {
        if (!$entity) {
            return null;
        }

        $currentIFace = $this->ifaceHelper->getCurrentIFace();
        $currentZone  = $currentIFace->getZoneName();

        if ($currentZone === $targetZone) {
            return null;
        }

        switch ($currentZone) {
            case IFaceZone::ADMIN_ZONE:
                // Show "Read in public" url
                return $this->ifaceHelper->getReadEntityUrl($entity, $targetZone);

            case IFaceZone::PUBLIC_ZONE:
                // Show "Edit in admin" url
                return $this->ifaceHelper->getReadEntityUrl($entity, $targetZone);

            default:
                return null;
        }
    }

    private function detectPrimaryEntity(): ?\Model_ContentPost
    {
        if ($post = $this->contentUrlParamHelper->getContentPost()) {
            return $post;
        }

//        if ($category = $this->contentUrlParamHelper->getContentCategory()) {
//            return $category;
//        }

        return null;
    }
}
