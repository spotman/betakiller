<?php
declare(strict_types=1);

namespace BetaKiller\Url\Zone;

use BetaKiller\Acl\Resource\AdminResource;
use BetaKiller\Model\RoleInterface;

class AdminZoneAccessSpec implements ZoneAccessSpecInterface
{
    /**
     * @return string[]
     */
    public function getAclRules(): array
    {
        return [
            AdminResource::SHORTCUT,
        ];
    }

    /**
     * @return string[]
     */
    public function getRolesNames(): array
    {
        return [
            RoleInterface::ADMIN_PANEL_ROLE_NAME,
        ];
    }

    /**
     * Returns true if some kind of protection is required for current zone (self acl rules or iface acl rules or iface entity)
     *
     * @return bool
     */
    public function isProtectionNeeded(): bool
    {
        return true;
    }

    /**
     * Returns true if authentication required for current zone
     *
     * @return bool
     */
    public function isAuthRequired(): bool
    {
        return true;
    }
}
