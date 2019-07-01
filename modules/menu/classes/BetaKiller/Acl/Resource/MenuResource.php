<?php
declare(strict_types=1);

namespace BetaKiller\Acl\Resource;

use BetaKiller\Model\RoleInterface;

class MenuResource extends AbstractCrudlsPermissionsResource
{
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
            self::ACTION_READ => [
                RoleInterface::LOGIN,
            ],
        ];
    }
}
