<?php

declare(strict_types=1);

namespace BetaKiller\Notification\Message;

use BetaKiller\Helper\UrlHelperInterface;
use BetaKiller\Model\ContentPostInterface;
use BetaKiller\Url\Zone;

final class ModeratorPostCompleteMessage extends AbstractBroadcastMessage
{
    use NotImplementedFactoryTrait;
    use NonCriticalMessageTrait;

    public static function getCodename(): string
    {
        return 'moderator/post/complete';
    }

    public static function createFrom(ContentPostInterface $post, UrlHelperInterface $urlHelper): self
    {
        return self::create([
            'url'   => $urlHelper->getReadEntityUrl($post, Zone::admin()),
            'label' => $post->getLabel(),
        ]);
    }
}
