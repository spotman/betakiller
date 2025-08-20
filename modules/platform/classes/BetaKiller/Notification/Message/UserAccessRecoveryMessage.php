<?php

declare(strict_types=1);

namespace BetaKiller\Notification\Message;

use BetaKiller\Model\TokenInterface;

final class UserAccessRecoveryMessage extends AbstractDirectMessage
{
    use NonCriticalMessageTrait;
    use NotImplementedFactoryTrait;

    public static function getCodename(): string
    {
        return 'email/user/access-recovery';
    }

    public static function createFrom(TokenInterface $token): self
    {
        return self::create([
            // User Language will be fetched from Token
            '$token' => $token,
        ]);
    }
}
