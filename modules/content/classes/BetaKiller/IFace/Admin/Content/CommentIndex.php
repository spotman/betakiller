<?php
namespace BetaKiller\IFace\Admin\Content;

class CommentIndex extends AbstractCommentList
{
    /**
     * @return \Model_ContentComment[]
     */
    protected function get_comments_list()
    {
        return $this->model_factory_content_comment()->get_latest_comments();
    }
}
