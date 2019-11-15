<?php
namespace BetaKiller\Widget\Admin\Menu;

use BetaKiller\Model\RoleInterface;
use BetaKiller\Widget\AbstractMenuWidget;

final class MainWidget extends AbstractMenuWidget
{
    /**
     * Returns array of roles` codenames which are allowed to use this widget
     *
     * @return string[]
     */
    public function getAclRoles(): array
    {
        return [
            // Admins only
            RoleInterface::ADMIN_PANEL,
        ];
    }
}
