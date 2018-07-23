<?php
declare(strict_types=1);

namespace BetaKiller\Url\Zone;

interface ZoneAccessSpecInterface
{
    public const NAMESPACES = ['Url', 'Zone'];
    public const SUFFIX     = 'ZoneAccessSpec';

    /**
     * @return string[]
     */
    public function getAclRules(): array;

    /**
     * @return string[]
     */
    public function getRolesNames(): array;

    /**
     * Returns true if some kind of protection is required for current zone (self acl rules or iface acl rules or iface entity)
     *
     * @return bool
     */
    public function isProtectionNeeded(): bool;

    /**
     * Returns true if authentication required for current zone
     *
     * @return bool
     */
    public function isAuthRequired(): bool;
}
