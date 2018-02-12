<?php
namespace BetaKiller\Widget\Admin;

use BetaKiller\Helper\IFaceHelper;
use BetaKiller\Helper\UrlContainerHelper;
use BetaKiller\IFace\CrudlsActionsInterface;
use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\IFace\IFaceInterface;
use BetaKiller\IFace\IFaceModelTree;
use BetaKiller\IFace\Widget\AbstractAdminWidget;
use BetaKiller\Model\DispatchableEntityInterface;
use BetaKiller\Model\IFaceZone;
use BetaKiller\Model\UserInterface;

class BarWidget extends AbstractAdminWidget
{
    /**
     * @var \BetaKiller\Url\UrlDispatcher
     */
    protected $dispatcher;

    /**
     * @var \BetaKiller\Helper\UrlContainerHelper
     */
    private $urlParamHelper;

    /**
     * @var \BetaKiller\IFace\IFaceModelTree
     */
    private $tree;

    /**
     * @Inject
     * @var \BetaKiller\Helper\ContentHelper
     */
    private $contentHelper;

    public function __construct(
        IFaceModelTree $tree,
        UserInterface $user,
        IFaceHelper $ifaceHelper,
        UrlContainerHelper $urlParamHelper
    ) {
        parent::__construct($user);

        $this->tree           = $tree;
        $this->ifaceHelper    = $ifaceHelper;
        $this->urlParamHelper = $urlParamHelper;
    }

    /**
     * Returns data for View rendering
     *
     * @return array
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \Spotman\Acl\Exception
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getData(): array
    {
        $currentIFaceModel = $this->ifaceHelper->getCurrentIFaceModel();
        $primaryEntity     = $currentIFaceModel ? $this->ifaceHelper->detectPrimaryEntity($currentIFaceModel) : null;

        $data = [
            'enabled'           => true,
            'comments'          => $this->getCommentsData(),
            'createButtonItems' => $this->getCreateButtonItems(),
            'primaryEntity'     => [
                'previewUrl' => $this->getPreviewButtonUrl($primaryEntity),
                'publicUrl'  => $this->getPublicReadButtonUrl($primaryEntity),
                'adminUrl'   => $this->getAdminEditButtonUrl($primaryEntity),
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
        $ifaces = $this->tree->getByActionAndZone(CrudlsActionsInterface::ACTION_CREATE, IFaceZone::ADMIN_ZONE);

        foreach ($ifaces as $ifaceModel) {
            if (!$this->aclHelper->isIFaceAllowed($ifaceModel)) {
                continue;
            }

            $items[] = [
                'label' => $ifaceModel->getLabel(),
                'url'   => $this->ifaceHelper->makeUrl($ifaceModel),
            ];
        }

        return $items;
    }

    /**
     * @return array|null
     * @throws \BetaKiller\Factory\FactoryException
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
            ->createSimple()
            ->setParameter($pendingStatus);

        $url = $this->aclHelper->isIFaceAllowed($iface->getModel(), $params)
            ? $this->ifaceHelper->makeUrl($iface->getModel(), $params)
            : null;

        return [
            'url'   => $url,
            'count' => $pendingCount,
        ];
    }

    /**
     * @uses \BetaKiller\IFace\Admin\Content\CommentListByStatus
     * @return \BetaKiller\IFace\IFaceInterface
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    private function getCommentsListByStatusIface(): IFaceInterface
    {
        return $this->ifaceHelper->createIFaceFromCodename('Admin_Content_CommentListByStatus');
    }

    /**
     * @uses \BetaKiller\IFace\Admin\Content\CommentIndex
     * @return \BetaKiller\IFace\IFaceInterface
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    private function getCommentsRootIface(): IFaceInterface
    {
        return $this->ifaceHelper->createIFaceFromCodename('Admin_Content_CommentIndex');
    }

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface|null $entity
     *
     * @return null|string
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    private function getAdminEditButtonUrl(?DispatchableEntityInterface $entity): ?string
    {
        return $this->getPrimaryEntityActionUrl($entity, IFaceZone::ADMIN_ZONE, CrudlsActionsInterface::ACTION_READ);
    }

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface|null $entity
     *
     * @return null|string
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    private function getPublicReadButtonUrl(?DispatchableEntityInterface $entity): ?string
    {
        return $this->getPrimaryEntityActionUrl($entity, IFaceZone::PUBLIC_ZONE, CrudlsActionsInterface::ACTION_READ);
    }

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface|null $entity
     *
     * @return null|string
     * @throws \BetaKiller\IFace\Exception\IFaceException
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
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    private function getPrimaryEntityActionUrl(
        ?DispatchableEntityInterface $entity,
        string $targetZone,
        string $targetAction
    ): ?string {
        if (!$entity) {
            return null;
        }

        if ($this->ifaceHelper->isCurrentIFaceZone($targetZone)) {
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
}
