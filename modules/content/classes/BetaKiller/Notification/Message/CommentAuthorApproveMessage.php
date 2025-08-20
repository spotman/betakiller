<?php

declare(strict_types=1);

namespace BetaKiller\Notification\Message;

use BetaKiller\Helper\UrlHelperInterface;
use BetaKiller\Model\ContentCommentInterface;
use BetaKiller\Model\ContentPostInterface;
use BetaKiller\Url\Zone;

final class CommentAuthorApproveMessage extends AbstractDirectMessage
{
    use NotImplementedFactoryTrait;
    use NonCriticalMessageTrait;

    public static function getCodename(): string
    {
        return 'email/user/comment/author-approve';
    }

    public static function createFrom(ContentCommentInterface $comment, UrlHelperInterface $urlHelper): self
    {
        return self::create([
            'name'       => $comment->getAuthorName(),
            'url'        => $comment->getPublicReadUrl($urlHelper),
            'created_at' => $comment->getCreatedAt()->format('H:i:s d.m.Y'),
            'label'      => $comment->getRelatedContentLabel(),
        ]);
    }
}
