<?php
namespace BetaKiller\IFace\Admin\Content;

class CommentListByStatus extends AbstractCommentList
{
    /**
     * @inheritDoc
     */
    protected function get_comments_list()
    {
        $status = $this->url_parameter_content_comment_status();

        return $this->model_factory_content_comment()->get_comments_by_status($status);
    }
}
