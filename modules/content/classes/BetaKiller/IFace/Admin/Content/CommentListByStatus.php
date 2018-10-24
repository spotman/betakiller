<?php
namespace BetaKiller\IFace\Admin\Content;

use BetaKiller\Helper\ContentUrlContainerHelper;
use BetaKiller\Repository\ContentCommentRepository;
use Psr\Http\Message\ServerRequestInterface;

class CommentListByStatus extends AbstractCommentList
{
    /**
     * @param \Psr\Http\Message\ServerRequestInterface        $request
     * @param \BetaKiller\Repository\ContentCommentRepository $repo
     *
     * @return \BetaKiller\Model\ContentComment[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    protected function getCommentsList(ServerRequestInterface $request, ContentCommentRepository $repo): array
    {
        $status = ContentUrlContainerHelper::getContentCommentStatus($request);

        return $repo->getLatestComments($status);
    }
}
