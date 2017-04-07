<?php

use BetaKiller\Helper\HasAdminUrlInterface;

class Widget_Admin_Bar extends \BetaKiller\IFace\Widget
{
    use BetaKiller\Helper\CurrentUserTrait;
    use BetaKiller\Helper\ContentTrait;

    /**
     * @var \URL_Dispatcher
     */
    protected $dispatcher;

    /**
     * Widget_Admin_Bar constructor.
     *
     * @param \URL_Dispatcher $dispatcher
     */
    public function __construct(URL_Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }


    /**
     * Returns data for View rendering
     *
     * @return array
     */
    public function get_data()
    {
        $user = $this->current_user(true);

        // If user is not authorized, then silently exiting
        if (!$user || !$user->is_admin_allowed()) {
            return [];
        }

        $data = [
            'enabled' => true,
            'comments'  =>  $this->getCommentsData(),
            'edit'  =>  [
                'url' => $this->getEditButtonUrl(),
            ],
        ];

        return $data;
    }

    protected function getCommentsData()
    {
        $commentOrm = $this->model_factory_content_comment();
        $statusOrm = $this->model_factory_content_comment_status();

        $status = $statusOrm->get_pending_status();
        $pendingCount = $commentOrm->get_comments_count($status);

        $url = $pendingCount
            ? $this->getCommentsListByStatusIfaceUrl($status)
            : $this->getCommentsRootIfaceUrl();

        return [
            'url' => $url,
            'count' => $pendingCount,
        ];
    }

    protected function getCommentsListByStatusIfaceUrl(Model_ContentCommentStatus $status)
    {
        /** @var \BetaKiller\IFace\Admin\Content\CommentListByStatus $iface */
        $iface = $this->iface_from_codename('Admin_Content_CommentListByStatus');

        $param = URL_Parameters::instance();
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

        if ($currentIFace instanceof BetaKiller\IFace\App\Content\PostItem) {
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
