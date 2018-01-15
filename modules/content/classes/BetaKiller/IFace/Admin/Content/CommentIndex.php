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
     * @throws \BetaKiller\Repository\RepositoryException
     */
    protected function getCommentsList(): array
    {
        return $this->commentRepository->getLatestComments();
    }
}
