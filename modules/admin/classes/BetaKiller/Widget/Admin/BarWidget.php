<?php
namespace BetaKiller\Widget\Admin;

use BetaKiller\CrudlsActionsInterface;
use BetaKiller\Helper\AclHelper;
use BetaKiller\Helper\IFaceHelper;
use BetaKiller\Helper\UrlContainerHelper;
use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\IFace\IFaceInterface;
use BetaKiller\Model\DispatchableEntityInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Url\UrlElementTreeInterface;
use BetaKiller\Url\ZoneInterface;
use BetaKiller\Widget\AbstractAdminWidget;

//use BetaKiller\Helper\ContentHelper;

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
     * @var \BetaKiller\Url\UrlElementTreeInterface
     */
    private $tree;

//    /**
//     * @var \BetaKiller\Helper\ContentHelper
//     */
//    private $contentHelper;

    /**
     * @var \BetaKiller\Helper\IFaceHelper
     */
    private $ifaceHelper;

    /**
     * @var \BetaKiller\Helper\AclHelper
     */
    private $aclHelper;

    /**
     * @var \BetaKiller\Model\UserInterface
     */
    private $user;

    public function __construct(
        UrlElementTreeInterface $tree,
        IFaceHelper $ifaceHelper,
        AclHelper $aclHelper,
        UserInterface $user,
        UrlContainerHelper $urlParamHelper
//        ContentHelper $contentHelper
    )
    {
        $this->tree           = $tree;
        $this->aclHelper      = $aclHelper;
        $this->ifaceHelper    = $ifaceHelper;
        $this->urlParamHelper = $urlParamHelper;
//        $this->contentHelper  = $contentHelper;
        $this->user = $user;
    }

    /**
     * Returns data for View rendering
     *
     * @return array
     * @throws \BetaKiller\Url\UrlPrototypeException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \Spotman\Acl\Exception
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getData(): array
    {
        return [
            'enabled'           => true,
//            'comments'          => $this->getCommentsData(),
            'comments'          => null,
            'createButtonItems' => $this->getCreateButtonItems(),
            'primaryEntity'     => $this->getPrimaryEntityData(),
        ];
    }

    public function isEmptyResponseAllowed(): bool
    {
        // If user is not authorized, then silently exiting
        return true;
    }

    protected function getPrimaryEntityData(): array
    {
        $currentUrlElement = $this->ifaceHelper->getCurrentIFaceModel();
        $primaryEntity     = $currentUrlElement ? $this->ifaceHelper->detectPrimaryEntity($currentUrlElement) : null;

        return [
            'previewUrl' => $this->getPreviewButtonUrl($primaryEntity),
            'publicUrl'  => $this->getPublicReadButtonUrl($primaryEntity),
            'adminUrl'   => $this->getAdminEditButtonUrl($primaryEntity),
        ];
    }

    /**
     * @return array
     * @throws \BetaKiller\Url\UrlPrototypeException
     * @throws \BetaKiller\IFace\Exception\IFaceException
     * @throws \Spotman\Acl\Exception
     */
    protected function getCreateButtonItems(): array
    {
        $items       = [];
        $urlElements = $this->tree->getIFacesByActionAndZone(CrudlsActionsInterface::ACTION_CREATE,
            ZoneInterface::ADMIN);

        foreach ($urlElements as $urlElement) {
            if (!$this->aclHelper->isUrlElementAllowed($this->user, $urlElement)) {
                continue;
            }

            $items[] = [
                'label' => $this->ifaceHelper->getLabel($urlElement),
                'url'   => $this->ifaceHelper->makeUrl($urlElement),
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
//    private function getCommentsData(): ?array
//    {
//        $pendingStatus = $this->contentHelper->getCommentStatusRepository()->getPendingStatus();
//        $pendingCount  = $this->contentHelper->getCommentRepository()->getCommentsCount($pendingStatus);
//
//        $iface = $pendingCount
//            ? $this->getCommentsListByStatusIface()
//            : $this->getCommentsRootIface();
//
//        $params = $this->urlParamHelper
//            ->createSimple()
//            ->setParameter($pendingStatus);
//
//        $url = $this->aclHelper->isIFaceAllowed($iface, $params)
//            ? $this->ifaceHelper->makeIFaceUrl($iface, $params)
//            : null;
//
//        return [
//            'url'   => $url,
//            'count' => $pendingCount,
//        ];
//    }

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
        return $this->getPrimaryEntityActionUrl($entity, ZoneInterface::ADMIN,
            CrudlsActionsInterface::ACTION_READ);
    }

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface|null $entity
     *
     * @return null|string
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    private function getPublicReadButtonUrl(?DispatchableEntityInterface $entity): ?string
    {
        return $this->getPrimaryEntityActionUrl($entity, ZoneInterface::PUBLIC,
            CrudlsActionsInterface::ACTION_READ);
    }

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface|null $entity
     *
     * @return null|string
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    private function getPreviewButtonUrl(?DispatchableEntityInterface $entity): ?string
    {
        return $this->getPrimaryEntityActionUrl($entity, ZoneInterface::PREVIEW,
            CrudlsActionsInterface::ACTION_READ);
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

        if ($this->ifaceHelper->isCurrentZone($targetZone)) {
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
