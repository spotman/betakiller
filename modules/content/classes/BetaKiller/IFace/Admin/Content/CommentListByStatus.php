<?php
namespace BetaKiller\IFace\Admin\Content;

class CommentListByStatus extends AbstractCommentList
{
    /**
     * @Inject
     * @var \BetaKiller\Helper\ContentUrlParametersHelper
     */
    private $urlParametersHelper;

    /**
     * @return \Model_ContentComment[]
     */
    protected function get_comments_list()
    {
        $status = $this->urlParametersHelper->getContentCommentStatus();

        return $this->model_factory_content_comment()->get_latest_comments($status);
    }
}
