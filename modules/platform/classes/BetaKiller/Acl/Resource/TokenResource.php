<?php
declare(strict_types=1);

namespace BetaKiller\Acl\Resource;

use BetaKiller\Model\RoleInterface;

final class TokenResource extends AbstractEntityRelatedAclResource
{
    /**
     * @inheritDoc
     */
    public function getDefaultAccessList(): array
    {
        return [
            self::ACTION_CREATE => [
                RoleInterface::LOGIN,
            ],
            self::ACTION_READ   => [
                // Allow tokens usage by authorized User (confirm email during sign-up process)
                RoleInterface::LOGIN,
                // Guests by default (for transparent log-in)
                RoleInterface::GUEST,
            ],
        ];
    }
}
