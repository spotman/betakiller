<?php
declare(strict_types=1);

namespace BetaKiller\Url\Zone;

use BetaKiller\Model\AbstractEntityInterface;

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

    /**
     * Returns true if entity is allowed in current zone or null if entity has no zone access definition
     *
     * @param \BetaKiller\Model\AbstractEntityInterface $entity
     *
     * @return bool
     */
    public function isEntityAllowed(AbstractEntityInterface $entity): ?bool;
}
