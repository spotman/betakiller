<?php
namespace BetaKiller\IFace\Admin\Content;

class CommentIndex extends AbstractCommentList
{
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
        return $this->commentRepository->getLatestComments();
    }
}
