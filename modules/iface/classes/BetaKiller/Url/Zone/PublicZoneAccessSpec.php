<?php
declare(strict_types=1);

namespace BetaKiller\Url\Zone;

class PublicZoneAccessSpec implements ZoneAccessSpecInterface
{
    /**
     * @return string[]
     */
    public function getAclRules(): array
    {
        // No additional rules for public zone
        return [];
    }

    /**
     * @return string[]
     */
    public function getRolesNames(): array
    {
        // Any user may access public zone
        return [];
    }

    /**
     * Returns true if some kind of protection is required for current zone (self acl rules or iface acl rules or iface entity)
     *
     * @return bool
     */
    public function isProtectionNeeded(): bool
    {
        // Public zone does not require protection nor authentication
        return false;
    }

    /**
     * Returns true if authentication required for current zone
     *
     * @return bool
     */
    public function isAuthRequired(): bool
    {
        return false;
    }
}
