<?php

namespace BetaKiller\Widget\Admin;

use BetaKiller\Helper\ContentTrait;
use BetaKiller\Helper\ContentUrlParametersHelper;
use BetaKiller\Helper\IFaceHelper;
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

    public function __construct(
        UserInterface $user,
        IFaceHelper $ifaceHelper,
        ContentUrlParametersHelper $cUrlParamHelper
    )
    {
        parent::__construct($user);

        $this->ifaceHelper           = $ifaceHelper;
        $this->contentUrlParamHelper = $cUrlParamHelper;
    }

    /**
     * Returns data for View rendering
     *
     * @return array
     */
    public function getData()
    {
        $data = [
            'enabled'  => true,
            'comments' => $this->getCommentsData(),
            'edit'     => [
                'url' => $this->getEditButtonUrl(),
            ],
        ];

        return $data;
    }

    protected function isEmptyResponseAllowed()
    {
        // If user is not authorized, then silently exiting
        return true;
    }

    protected function getCommentsData()
    {
        $commentOrm = $this->model_factory_content_comment();
        $statusOrm  = $this->model_factory_content_comment_status();

        $status       = $statusOrm->get_pending_status();
        $pendingCount = $commentOrm->get_comments_count($status);

        $url = $pendingCount
            ? $this->getCommentsListByStatusIfaceUrl($status)
            : $this->getCommentsRootIfaceUrl();

        return [
            'url'   => $url,
            'count' => $pendingCount,
        ];
    }

    protected function getCommentsListByStatusIfaceUrl(Model_ContentCommentStatus $status)
    {
        /** @var \BetaKiller\IFace\Admin\Content\CommentListByStatus $iface */
        $iface = $this->ifaceHelper->createIFaceFromCodename('Admin_Content_CommentListByStatus');

        $param = $this->contentUrlParamHelper->createEmpty();

        $param->setEntity($status);

        return $iface->url($param);
    }

    protected function getCommentsRootIfaceUrl()
    {
        /** @var \BetaKiller\IFace\Admin\Content\CommentIndex $iface */
        $iface = $this->ifaceHelper->createIFaceFromCodename('Admin_Content_CommentIndex');

        return $iface->url();
    }

    protected function getEditButtonUrl()
    {
        $entity = $this->detectPrimaryEntity();

        if (!$entity) {
            return null;
        }

        $currentIFace = $this->ifaceHelper->getCurrentIFace();
        $currentZone = $currentIFace->getZoneName();

        switch ($currentZone) {
            case IFaceZone::ADMIN_ZONE:
                // Show "Read in public" url
                return $this->ifaceHelper->getReadEntityUrl($entity, IFaceZone::PUBLIC_ZONE);

            case IFaceZone::PUBLIC_ZONE:
                // Show "Edit in admin" url
                return $this->ifaceHelper->getUpdateEntityUrl($entity, IFaceZone::ADMIN_ZONE);

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
