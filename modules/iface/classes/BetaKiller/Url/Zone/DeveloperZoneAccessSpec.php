<?php
declare(strict_types=1);

namespace BetaKiller\Url\Zone;

use BetaKiller\Model\RoleInterface;

class DeveloperZoneAccessSpec implements ZoneAccessSpecInterface
{
    /**
     * @return string[]
     */
    public function getAclRules(): array
    {
        // No additional rules
        return [];
    }

    /**
     * @return string[]
     */
    public function getRolesNames(): array
    {
        return [
            // Only developers may access personal zone
            RoleInterface::DEVELOPER_ROLE_NAME,
        ];
    }

    /**
     * Returns true if some kind of protection is required for current zone (self acl rules or iface acl rules or iface entity)
     *
     * @return bool
     */
    public function isProtectionNeeded(): bool
    {
        // Developer zone does not require protection, only authentication
        return false;
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
