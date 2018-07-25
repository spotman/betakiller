<?php
declare(strict_types=1);

namespace BetaKiller\Url\Zone;

class PreviewZoneAccessSpec implements ZoneAccessSpecInterface
{
    /**
     * @return string[]
     */
    public function getAclRules(): array
    {
        // No special rules
        return [];
    }

    /**
     * @return string[]
     */
    public function getRolesNames(): array
    {
        return [];
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
