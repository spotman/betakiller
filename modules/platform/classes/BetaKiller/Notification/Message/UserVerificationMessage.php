<?php

declare(strict_types=1);

namespace BetaKiller\Notification\Message;

use BetaKiller\Model\TokenInterface;

final class UserVerificationMessage extends AbstractDirectMessage
{
    use NonCriticalMessageTrait;
    use NotImplementedFactoryTrait;

    public static function getCodename(): string
    {
        return 'email/user/verification';
    }

    public static function createFrom(TokenInterface $token): self
    {
        return self::create([
            // For action URL generation
            '$token' => $token,
        ]);
    }
}
