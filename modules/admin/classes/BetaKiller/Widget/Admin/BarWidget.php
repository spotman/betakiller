<?php
namespace BetaKiller\Widget\Admin;

use BetaKiller\Helper\IFaceHelper;
use BetaKiller\Helper\UrlContainerHelper;
use BetaKiller\IFace\CrudlsActionsInterface;
use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\IFace\IFaceInterface;
use BetaKiller\IFace\IFaceProvider;
use BetaKiller\IFace\Widget\AbstractAdminWidget;
use BetaKiller\Model\ContentCategory;
use BetaKiller\Model\ContentPost;
use BetaKiller\Model\DispatchableEntityInterface;
use BetaKiller\Model\IFaceZone;
use BetaKiller\Model\LayoutInterface;
use BetaKiller\Model\UserInterface;

class BarWidget extends AbstractAdminWidget
{
    /**
     * @var \BetaKiller\IFace\Url\UrlDispatcher
     */
    protected $dispatcher;

    /**
     * @var \BetaKiller\Helper\UrlContainerHelper
     */
    private $urlParamHelper;

    /**
     * @var \BetaKiller\IFace\IFaceProvider
     */
    private $ifaceProvider;

    /**
     * @Inject
     * @var \BetaKiller\Helper\ContentHelper
     */
    private $contentHelper;

    public function __construct(
        UserInterface $user,
        IFaceHelper $ifaceHelper,
        UrlContainerHelper $urlParamHelper,
        IFaceProvider $ifaceProvider
    ) {
        parent::__construct($user);

        $this->ifaceHelper    = $ifaceHelper;
        $this->urlParamHelper = $urlParamHelper;
        $this->ifaceProvider  = $ifaceProvider;
    }

    /**
     * Returns data for View rendering
     *
     * @return array
     * @throws \BetaKiller\Exception
     * @throws \Spotman\Acl\Exception
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getData(): array
    {
        $currentIFace  = $this->ifaceHelper->getCurrentIFace();
        $currentLayout = $currentIFace ? $currentIFace->getLayoutCodename() : null;
        $isAdminLayout = $currentLayout === LayoutInterface::LAYOUT_ADMIN;

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

    /**
     * @return array
     * @throws \BetaKiller\IFace\Exception\IFaceException
     * @throws \Spotman\Acl\Exception
     */
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

    /**
     * @return array|null
     * @throws \BetaKiller\IFace\Exception\IFaceException
     * @throws \Spotman\Acl\Exception
     */
    private function getCommentsData(): ?array
    {
        $pendingStatus = $this->contentHelper->getCommentStatusRepository()->getPendingStatus();
        $pendingCount  = $this->contentHelper->getCommentRepository()->getCommentsCount($pendingStatus);

        $iface = $pendingCount
            ? $this->getCommentsListByStatusIface()
            : $this->getCommentsRootIface();

        $params = $this->urlParamHelper
            ->createEmpty()
            ->setParameter($pendingStatus);

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

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface|null $entity
     *
     * @return null|string
     * @throws \BetaKiller\Exception
     */
    private function getAdminEditButtonUrl(?DispatchableEntityInterface $entity): ?string
    {
        return $this->getPrimaryEntityActionUrl($entity, IFaceZone::ADMIN_ZONE, CrudlsActionsInterface::ACTION_READ);
    }

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface|null $entity
     *
     * @return null|string
     * @throws \BetaKiller\Exception
     */
    private function getPublicReadButtonUrl(?DispatchableEntityInterface $entity): ?string
    {
        return $this->getPrimaryEntityActionUrl($entity, IFaceZone::PUBLIC_ZONE, CrudlsActionsInterface::ACTION_READ);
    }

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface|null $entity
     *
     * @return null|string
     * @throws \BetaKiller\Exception
     */
    private function getPreviewButtonUrl(?DispatchableEntityInterface $entity): ?string
    {
        return $this->getPrimaryEntityActionUrl($entity, IFaceZone::PREVIEW_ZONE, CrudlsActionsInterface::ACTION_READ);
    }

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface|null $entity
     * @param string                                             $targetZone
     * @param string                                             $targetAction
     *
     * @return null|string
     * @throws \BetaKiller\Exception
     */
    private function getPrimaryEntityActionUrl(
        ?DispatchableEntityInterface $entity,
        string $targetZone,
        string $targetAction
    ): ?string {
        if (!$entity) {
            return null;
        }

        $currentIFace = $this->ifaceHelper->getCurrentIFace();
        $currentZone  = $currentIFace ? $currentIFace->getZoneName() : IFaceZone::PUBLIC_ZONE;

        if ($currentZone === $targetZone) {
            return null;
        }

        try {
            return $this->ifaceHelper->getEntityUrl($entity, $targetAction, $targetZone);
        } /** @noinspection BadExceptionsProcessingInspection */
        catch (IFaceException $e) {
            // No IFace found for provided zone/action
            return null;
        }
    }

    private function detectPrimaryEntity(): ?DispatchableEntityInterface
    {
        foreach ($this->getPrimaryEntitiesClassNames() as $className) {
            if ($entity = $this->urlParamHelper->getEntityByClassName($className)) {
                return $entity;
            }
        }

        return null;
    }

    private function getPrimaryEntitiesClassNames(): array
    {
        return [
            ContentPost::class,
            ContentCategory::class,
        ];
    }
}
