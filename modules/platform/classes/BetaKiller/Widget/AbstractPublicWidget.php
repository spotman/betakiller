<?php
declare(strict_types=1);

namespace BetaKiller\Widget;

use BetaKiller\Model\RoleInterface;

abstract class AbstractPublicWidget extends AbstractWidget
{
    /**
     * Returns array of roles` codenames which are allowed to use this widget
     *
     * @return string[]
     */
    public function getAclRoles(): array
    {
        return [
            // Guests and any logged in users may use public widgets
            RoleInterface::GUEST,
        ];
    }

    /**
     * Returns true if current widget may be omitted during the render process
     *
     * @return bool
     */
    public function isEmptyResponseAllowed(): bool
    {
        // Public widgets may have empty body
        return true;
    }
}
