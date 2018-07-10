<?php
declare(strict_types=1);

namespace BetaKiller\Widget;

use BetaKiller\Model\RoleInterface;

abstract class AbstractPublicWidget extends AbstractWidget
{
    /**
     * Returns array of roles` codenames which are allowed to use this widget
     *
     * @return array
     */
    public function getAclRoles(): array
    {
        return [
            // Guests and any logged in users may use public widgets
            RoleInterface::GUEST_ROLE_NAME,
            RoleInterface::LOGIN_ROLE_NAME,
        ];
    }

    public function isEmptyResponseAllowed(): bool
    {
        // Public widgets may have empty body
        return true;
    }
}
