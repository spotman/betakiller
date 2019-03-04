<?php
declare(strict_types=1);

namespace BetaKiller\Acl\Resource;

use BetaKiller\Model\RoleInterface;
use Spotman\Acl\Resource\AbstractResolvingResource;

class AuthResource extends AbstractResolvingResource
{
    private const ACTION_PASSWORD_CHANGE = 'requestPasswordChange';

    /**
     * Returns default permissions bundled with current resource
     * Key=>Value pairs where key is a permission identity and value is an array of roles
     * Useful for presetting permissions for resources with fixed access control list or permissions based on hard-coded logic
     *
     * @return string[][]
     */
    public function getDefaultAccessList(): array
    {
        return [
            self::ACTION_PASSWORD_CHANGE => [
                RoleInterface::LOGIN,
            ],
        ];
    }
}
