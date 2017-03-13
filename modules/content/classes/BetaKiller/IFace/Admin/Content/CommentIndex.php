<?php
namespace BetaKiller\IFace\Admin\Content;

class CommentIndex extends AdminBase
{
    /**
     * Returns data for View
     * Override this method in child classes
     */
    public function get_data()
    {
        // Redirect to pending comments

        $status = $this->model_factory_content_comment_status()->get_pending_status();

        /** @var CommentListByStatus $iface */
        $iface = $this->iface_from_codename('Admin_Content_CommentListByStatus');

        $params = $this->url_parameters_instance()->set($status::URL_PARAM, $status);

        $url = $iface->url($params);

        $this->redirect($url);
    }
}
