<?php
namespace BetaKiller\IFace\Admin\Content;

class CommentListByStatus extends AbstractCommentList
{
    /**
     * @Inject
     * @var \BetaKiller\Helper\ContentUrlContainerHelper
     */
    private $urlParametersHelper;

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
        $status = $this->urlParametersHelper->getContentCommentStatus();

        return $this->commentRepository->getLatestComments($status);
    }
}
