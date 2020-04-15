<?php
namespace BetaKiller\Helper;

use BetaKiller\Model\ContentCategoryInterface;
use BetaKiller\Model\ContentCommentInterface;
use BetaKiller\Model\ContentCommentState;
use BetaKiller\Model\ContentPostInterface;
use BetaKiller\Model\ContentPostRevision;
use Psr\Http\Message\ServerRequestInterface;

class ContentUrlContainerHelper
{
    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \BetaKiller\Model\ContentCategoryInterface|null
     */
    public static function getContentCategory(ServerRequestInterface $request): ?ContentCategoryInterface
    {
        return ServerRequestHelper::getEntity($request, ContentCategoryInterface::class);
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \BetaKiller\Model\ContentPostInterface
     */
    public static function getContentPost(ServerRequestInterface $request): ContentPostInterface
    {
        return ServerRequestHelper::getEntity($request, ContentPostInterface::class);
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
     * @return \BetaKiller\Model\ContentCommentState
     */
    public static function getContentCommentStatus(ServerRequestInterface $request): ContentCommentState
    {
        return ServerRequestHelper::getEntity($request, ContentCommentState::class);
    }
}
