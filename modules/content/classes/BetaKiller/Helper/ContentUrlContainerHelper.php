<?php
namespace BetaKiller\Helper;

use BetaKiller\Model\ContentCategoryInterface;
use BetaKiller\Model\ContentCommentInterface;
use BetaKiller\Model\ContentCommentStatus;
use BetaKiller\Model\ContentPost;
use BetaKiller\Model\ContentPostRevision;
use Psr\Http\Message\ServerRequestInterface;

class ContentUrlContainerHelper
{
    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \BetaKiller\Model\ContentCategoryInterface
     */
    public static function getContentCategory(ServerRequestInterface $request): ContentCategoryInterface
    {
        return ServerRequestHelper::getEntity($request, ContentCategoryInterface::class);
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \BetaKiller\Model\ContentPost
     */
    public static function getContentPost(ServerRequestInterface $request): ContentPost
    {
        return ServerRequestHelper::getEntity($request, ContentPost::class);
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \BetaKiller\Model\ContentPostRevision
     */
    public static function getContentPostRevision(ServerRequestInterface $request): ContentPostRevision
    {
        return ServerRequestHelper::getEntity($request, ContentPostRevision::class);
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \BetaKiller\Model\ContentCommentInterface
     */
    public static function getContentComment(ServerRequestInterface $request): ContentCommentInterface
    {
        return ServerRequestHelper::getEntity($request, ContentCommentInterface::class);
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \BetaKiller\Model\ContentCommentStatus
     */
    public static function getContentCommentStatus(ServerRequestInterface $request): ContentCommentStatus
    {
        return ServerRequestHelper::getEntity($request, ContentCommentStatus::class);
    }
}
