<?php
declare(strict_types=1);

namespace BetaKiller\Acl\Resource;

use BetaKiller\Model\RoleInterface;

final class TokenResource extends AbstractCrudlsPermissionsResource
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
            self::ACTION_READ => [
                RoleInterface::GUEST,
            ],
        ];
    }
}
