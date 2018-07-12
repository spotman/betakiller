<?php
declare(strict_types=1);

namespace BetaKiller\Widget;

use BetaKiller\Model\RoleInterface;

abstract class AbstractAdminWidget extends AbstractWidget
{
    /**
     * Returns array of roles` codenames which are allowed to use this widget
     *
     * @return string[]
     */
    public function getAclRoles(): array
    {
        return [
            RoleInterface::ADMIN_ROLE_NAME
        ];
    }

    public function isEmptyResponseAllowed(): bool
    {
        // Admin widgets must have body
        return false;
    }
}
