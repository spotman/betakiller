<?php

namespace BetaKiller\Widget\Admin;

use BetaKiller\Model\UserInterface;
use BetaKiller\IFace\Widget\AbstractAdminWidget;
use BetaKiller\IFace\Url\UrlDispatcher;
use BetaKiller\IFace\Url\UrlParameters;
use Model_ContentCommentStatus;
use Model_ContentPost;
use BetaKiller\Helper\ContentTrait;

class BarWidget extends AbstractAdminWidget
{
    use ContentTrait;

    /**
     * @var \BetaKiller\IFace\Url\UrlDispatcher
     */
    protected $dispatcher;


    /**
     * BarWidget constructor.
     *
     * @param \BetaKiller\Model\UserInterface $user
     * @param \BetaKiller\IFace\Url\UrlDispatcher $dispatcher
     */
    public function __construct(UserInterface $user, UrlDispatcher $dispatcher)
    {
        parent::__construct($user);
        $this->dispatcher = $dispatcher;
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
        $iface = $this->iface_from_codename('Admin_Content_CommentListByStatus');

        $param = UrlParameters::create();
        $param->set($status::URL_PARAM, $status);

        return $iface->url($param);
    }

    protected function getCommentsRootIfaceUrl()
    {
        /** @var \BetaKiller\IFace\Admin\Content\CommentIndex $iface */
        $iface = $this->iface_from_codename('Admin_Content_CommentIndex');

        return $iface->url();
    }

    protected function getEditButtonUrl()
    {
        // TODO Detect editable via publicIFace->model->adminIface link
        $currentIFace = $this->dispatcher->currentIFace();

        $parameters = $this->dispatcher->parameters();

        if ($currentIFace instanceof \BetaKiller\IFace\App\Content\PostItem) {
            /** @var Model_ContentPost $model */
            $model = $parameters->get(Model_ContentPost::URL_PARAM);

            return $model->get_admin_url();
        }

//        if ($currentIFace instanceof BetaKiller\IFace\App\Content\CategoryItem) {
//            /** @var \Model_ContentCategory $model */
//            $model = $parameters->get(Model_ContentCategory::URL_PARAM);
//            return $model->get_admin_url();
//        }

        return null;
    }
}
