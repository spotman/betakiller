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
     * @Inject
     * @var \BetaKiller\Repository\ContentCommentRepository
     */
    private $commentRepository;

    /**
     * @return \BetaKiller\Model\ContentComment[]
     */
    protected function get_comments_list(): array
    {
        $status = $this->urlParametersHelper->getContentCommentStatus();

        return $this->commentRepository->getLatestComments($status);
    }
}
