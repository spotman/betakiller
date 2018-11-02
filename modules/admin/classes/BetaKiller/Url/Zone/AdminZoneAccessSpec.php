<?php
declare(strict_types=1);

namespace BetaKiller\Url\Zone;

use BetaKiller\Acl\Resource\AdminResource;
use BetaKiller\Model\AbstractEntityInterface;
use BetaKiller\Model\HasAdminZoneAccessSpecInterface;
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
            RoleInterface::ADMIN_PANEL,
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

    /**
     * Returns true if entity is allowed in current zone or null if entity has no zone access definition
     *
     * @param \BetaKiller\Model\AbstractEntityInterface $entity
     *
     * @return bool
     */
    public function isEntityAllowed(AbstractEntityInterface $entity): ?bool
    {
        if (!$entity instanceof HasAdminZoneAccessSpecInterface) {
            // Undetermined
            return null;
        }

        return $entity->isAdminZoneAccessAllowed();
    }
}
