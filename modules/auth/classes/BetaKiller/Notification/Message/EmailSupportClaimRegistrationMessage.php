<?php

declare(strict_types=1);

namespace BetaKiller\Notification\Message;

use BetaKiller\Factory\UrlHelperFactory;
use BetaKiller\Helper\UrlHelperInterface;
use BetaKiller\Model\NotificationLogInterface;
use BetaKiller\Notification\MessageTargetInterface;
use BetaKiller\Repository\NotificationLogRepositoryInterface;
use BetaKiller\Url\Zone;

final class EmailSupportClaimRegistrationMessage extends AbstractBroadcastMessage
{
    public static function getCodename(): string
    {
        return 'email/support/claim-registration';
    }

    public static function isCritical(): bool
    {
        return false;
    }

    public static function createFrom(NotificationLogInterface $log, UrlHelperInterface $urlHelper, string $ip): self
    {
        return self::create([
            'email'             => $log->getTargetIdentity(),
            'ip'                => $ip,
            'notification_url'  => $urlHelper->getReadEntityUrl($log, Zone::admin()),
            'notification_hash' => $log->getHash(),
        ]);
    }

    public static function getFactoryFor(MessageTargetInterface $target): callable
    {
        return function (NotificationLogRepositoryInterface $logRepo, UrlHelperFactory $urlHelperFactory) {
            return self::createFrom(
                $logRepo->getLast(),
                $urlHelperFactory->create(),
                '8.8.8.8'
            );
        };
    }
}
