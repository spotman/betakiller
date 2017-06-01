<?php

namespace BetaKiller\Widget\Admin;

use BetaKiller\Helper\ContentTrait;
use BetaKiller\Helper\ContentUrlParametersHelper;
use BetaKiller\Helper\IFaceHelper;
use BetaKiller\IFace\CrudlsActionsInterface;
use BetaKiller\IFace\IFaceProvider;
use BetaKiller\IFace\Widget\AbstractAdminWidget;
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
    public function getData()
    {
        $isAdminZone = $this->ifaceHelper->getCurrentIFace()->getZoneName() === IFaceZone::ADMIN_ZONE;

            $data = [
            'isAdminZone'       => $isAdminZone,
            'enabled'           => true,
            'comments'          => $this->getCommentsData(),
            'createButtonItems' => $this->getCreateButtonItems(),
            'primaryEntity'     => [
                'publicUrl' => $this->getPublicReadButtonUrl(),
                'adminUrl'  => $this->getAdminEditButtonUrl(),
            ],
        ];

        // TODO Preview post button

        return $data;
    }

    protected function isEmptyResponseAllowed()
    {
        // If user is not authorized, then silently exiting
        return true;
    }

    private function getCreateButtonItems()
    {
        $items  = [];
        $ifaces = $this->ifaceProvider->getByActionAndZone(CrudlsActionsInterface::CREATE_ACTION, IFaceZone::ADMIN_ZONE);

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

    private function getCommentsData()
    {
        $commentOrm = $this->model_factory_content_comment();
        $statusOrm  = $this->model_factory_content_comment_status();

        $status       = $statusOrm->getPendingStatus();
        $pendingCount = $commentOrm->getCommentsCount($status);

        /** @var \BetaKiller\Acl\Resource\ContentCommentResource $resource */
        $resource = $this->aclHelper->getResource('ContentComment');

        if (!$resource->isStatusActionAllowed($status, $resource::UPDATE_ACTION)) {
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

    private function getCommentsListByStatusIfaceUrl(Model_ContentCommentStatus $status)
    {
        /** @var \BetaKiller\IFace\Admin\Content\CommentListByStatus $iface */
        $iface = $this->ifaceHelper->createIFaceFromCodename('Admin_Content_CommentListByStatus');

        $param = $this->contentUrlParamHelper->createEmpty();

        $param->setEntity($status);

        return $iface->url($param);
    }

    private function getCommentsRootIfaceUrl()
    {
        /** @var \BetaKiller\IFace\Admin\Content\CommentIndex $iface */
        $iface = $this->ifaceHelper->createIFaceFromCodename('Admin_Content_CommentIndex');

        return $iface->url();
    }

    private function getAdminEditButtonUrl()
    {
        return $this->getPrimaryEntityActionUrl(IFaceZone::ADMIN_ZONE);
    }

    private function getPublicReadButtonUrl()
    {
        return $this->getPrimaryEntityActionUrl(IFaceZone::PUBLIC_ZONE);
    }

    private function getPrimaryEntityActionUrl($targetZone)
    {
        $entity = $this->detectPrimaryEntity();

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

    private function detectPrimaryEntity()
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
