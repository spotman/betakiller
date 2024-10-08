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
            RoleInterface::ADMIN_PANEL
        ];
    }

    /**
     * Returns true if current widget may be omitted during the render process
     *
     * @return bool
     */
    public function isEmptyResponseAllowed(): bool
    {
        // Admin widgets must have body
        return false;
    }
}
