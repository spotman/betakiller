<?php

declare(strict_types=1);

namespace BetaKiller\Notification\Message;

use BetaKiller\Helper\UrlHelperInterface;
use BetaKiller\Model\ContentCommentInterface;

final class CommentAuthorReplyMessage extends AbstractDirectMessage
{
    use NotImplementedFactoryTrait;
    use NonCriticalMessageTrait;

    public static function getCodename(): string
    {
        return 'email/user/comment/parent-author-reply';
    }

    public static function createFrom(ContentCommentInterface $reply, UrlHelperInterface $urlHelper): self
    {
        return self::create([
            'url'        => $reply->getPublicReadUrl($urlHelper),
            'created_at' => $reply->getCreatedAt()->format('H:i:s d.m.Y'),
            'label'      => $reply->getRelatedContentLabel(),
        ]);
    }
}
