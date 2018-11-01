<?php
declare(strict_types=1);

use BetaKiller\Model\RoleInterface;

return [
    RoleInterface::ROOT_ROLE_NAME      => 'Root role, inherit all other roles permissions',
    RoleInterface::DEVELOPER_ROLE_NAME => 'Role for developers',
    RoleInterface::MODERATOR_ROLE_NAME => 'Role for moderators',
    RoleInterface::LOGIN_ROLE_NAME     => 'Grants access to login',
    RoleInterface::GUEST_ROLE_NAME     => 'All guests and unauthorized users',

    RoleInterface::ADMIN_PANEL_ROLE_NAME => 'Grants access to the admin panel',
];
