<?php
namespace BetaKiller\IFace\Admin\Content;

use BetaKiller\Repository\ContentCommentRepository;
use Psr\Http\Message\ServerRequestInterface;

class CommentIndex extends AbstractCommentList
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
        return $repo->getLatestComments();
    }
}
