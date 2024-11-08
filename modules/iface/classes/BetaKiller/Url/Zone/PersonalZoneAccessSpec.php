<?php
declare(strict_types=1);

namespace BetaKiller\Url\Zone;

use BetaKiller\Model\AbstractEntityInterface;
use BetaKiller\Model\HasPersonalZoneAccessSpecInterface;
use BetaKiller\Model\RoleInterface;

class PersonalZoneAccessSpec implements ZoneAccessSpecInterface
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
            // Any logged-in user may access personal zone
            RoleInterface::LOGIN,
        ];
    }

    /**
     * Returns true if some kind of protection is required for current zone (self acl rules or iface acl rules or iface entity)
     *
     * @return bool
     */
    public function isProtectionNeeded(): bool
    {
        // Personal zone does not require protection, only authentication
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

    /**
     * Returns true if entity is allowed in current zone or null if entity has no zone access definition
     *
     * @param \BetaKiller\Model\AbstractEntityInterface $entity
     *
     * @return bool
     */
    public function isEntityAllowed(AbstractEntityInterface $entity): ?bool
    {
        if (!$entity instanceof HasPersonalZoneAccessSpecInterface) {
            // Undetermined
            return null;
        }

        return $entity->isPersonalZoneAccessAllowed();
    }
}
