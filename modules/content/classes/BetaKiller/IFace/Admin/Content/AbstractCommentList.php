<?php

namespace BetaKiller\IFace\Admin\Content;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\UrlHelperInterface;
use BetaKiller\Model\ContentCommentInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\ContentCommentRepository;
use BetaKiller\Url\Zone;
use Psr\Http\Message\ServerRequestInterface;

abstract readonly class AbstractCommentList extends AbstractContentAdminIFace
{
    /**
     * AbstractCommentList constructor.
     *
     * @param \BetaKiller\Repository\ContentCommentRepository $commentRepo
     */
    public function __construct(private ContentCommentRepository $commentRepo)
    {
    }

    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     * @throws \BetaKiller\Url\UrlElementException
     */
    public function getData(ServerRequestInterface $request): array
    {
        $user      = ServerRequestHelper::getUser($request);
        $urlHelper = ServerRequestHelper::getUrlHelper($request);

        $comments = $this->getCommentsList($request, $this->commentRepo);

        $data = [];

        foreach ($comments as $comment) {
            $data[] = $this->getCommentData($comment, $urlHelper, $user);
        }

        return [
            'comments' => $data,
        ];
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface        $request
     * @param \BetaKiller\Repository\ContentCommentRepository $repo
     *
     * @return \BetaKiller\Model\ContentCommentInterface[]
     */
    abstract protected function getCommentsList(ServerRequestInterface $request, ContentCommentRepository $repo): array;

    protected function getCommentData(
        ContentCommentInterface $comment,
        UrlHelperInterface $helper,
        UserInterface $user
    ): array {
        $status = $comment->getWorkflowState();

        return [
            'id'           => $comment->getID(),
            'publicURL'    => $comment->getPublicReadUrl($helper),
            // Get public URL via related model
            'editURL'      => $helper->getReadEntityUrl($comment, Zone::admin()),
            // Get admin URL via related model
            'contentLabel' => $comment->getRelatedContentLabel(),
            'author'       => [
                'isGuest' => $comment->authorIsGuest(),
                'name'    => $comment->getAuthorName(),
                'email'   => $comment->getAuthorEmail(),
                'ip'      => $comment->getIpAddress(),
                'agent'   => $comment->getUserAgent(),
            ],
            'status'       => [
                'id'          => $status->getID(),
                'codename'    => $status->getCodename(),
                'transitions' => $status->getAllowedTargetTransitionsCodenameArray($user),
            ],
            'message'      => $comment->getMessage(),
            'preview'      => \Text::limit_chars($comment->getMessage(), 300, null, true),
        ];
    }
}
