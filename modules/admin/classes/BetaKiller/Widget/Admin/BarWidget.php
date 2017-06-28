<?php

namespace BetaKiller\Widget\Admin;

use BetaKiller\Helper\ContentTrait;
use BetaKiller\Helper\ContentUrlParametersHelper;
use BetaKiller\Helper\IFaceHelper;
use BetaKiller\IFace\CrudlsActionsInterface;
use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\IFace\IFaceInterface;
use BetaKiller\IFace\IFaceProvider;
use BetaKiller\IFace\Widget\AbstractAdminWidget;
use BetaKiller\Model\DispatchableEntityInterface;
use BetaKiller\Model\IFaceZone;
use BetaKiller\Model\Layout;
use BetaKiller\Model\UserInterface;

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
    ) {
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
        $currentIFace  = $this->ifaceHelper->getCurrentIFace();
        $currentLayout = $currentIFace->getLayoutCodename();
        $isAdminLayout = $currentLayout === Layout::LAYOUT_ADMIN;

        $entity = $this->detectPrimaryEntity();

        $data = [
            'isAdminLayout'     => $isAdminLayout,
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
        $ifaces = $this->ifaceProvider->getByActionAndZone(CrudlsActionsInterface::ACTION_CREATE,
            IFaceZone::ADMIN_ZONE);

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

        $pendingStatus = $statusOrm->getPendingStatus();
        $pendingCount  = $commentOrm->getCommentsCount($pendingStatus);

        $iface = $pendingCount
            ? $this->getCommentsListByStatusIface()
            : $this->getCommentsRootIface();

        $params = $this->contentUrlParamHelper
            ->createEmpty()
            ->setEntity($pendingStatus);

        $url = $this->aclHelper->isIFaceAllowed($iface, $params) ? $iface->url($params) : null;

        return [
            'url'   => $url,
            'count' => $pendingCount,
        ];
    }

    /**
     * @uses \BetaKiller\IFace\Admin\Content\CommentListByStatus
     * @return \BetaKiller\IFace\IFaceInterface
     */
    private function getCommentsListByStatusIface(): IFaceInterface
    {
        return $this->ifaceHelper->createIFaceFromCodename('Admin_Content_CommentListByStatus');
    }

    /**
     * @uses \BetaKiller\IFace\Admin\Content\CommentIndex
     * @return \BetaKiller\IFace\IFaceInterface
     */
    private function getCommentsRootIface(): IFaceInterface
    {
        return $this->ifaceHelper->createIFaceFromCodename('Admin_Content_CommentIndex');
    }

    private function getAdminEditButtonUrl(?DispatchableEntityInterface $entity): ?string
    {
        return $this->getPrimaryEntityActionUrl($entity, IFaceZone::ADMIN_ZONE, CrudlsActionsInterface::ACTION_READ);
    }

    private function getPublicReadButtonUrl(?DispatchableEntityInterface $entity): ?string
    {
        return $this->getPrimaryEntityActionUrl($entity, IFaceZone::PUBLIC_ZONE, CrudlsActionsInterface::ACTION_READ);
    }

    private function getPreviewButtonUrl(?DispatchableEntityInterface $entity): ?string
    {
        return $this->getPrimaryEntityActionUrl($entity, IFaceZone::PREVIEW_ZONE, CrudlsActionsInterface::ACTION_READ);
    }

    private function getPrimaryEntityActionUrl(
        ?DispatchableEntityInterface $entity,
        string $targetZone,
        string $targetAction
    ): ?string
    {
        if (!$entity) {
            return null;
        }

        $currentIFace = $this->ifaceHelper->getCurrentIFace();
        $currentZone  = $currentIFace->getZoneName();

        if ($currentZone === $targetZone) {
            return null;
        }

        try {
            return $this->ifaceHelper->getEntityUrl($entity, $targetAction, $targetZone);
        } catch (IFaceException $e) {
            // No IFace found for provided zone/action
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
