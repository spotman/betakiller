<?php
declare(strict_types=1);

use BetaKiller\Auth\RoleConfig;
use BetaKiller\Model\RoleInterface;

return [
    RoleInterface::GUEST => [
        RoleConfig::OPTION_DESC => 'All guests and unauthorized users',
    ],

    RoleInterface::LOGIN => [
        RoleConfig::OPTION_DESC     => 'Grants access to login',
        RoleConfig::OPTION_INHERITS => [
            RoleInterface::GUEST,
        ],
    ],

    RoleInterface::MODERATOR => [
        RoleConfig::OPTION_DESC => 'Role for moderators',
    ],

    RoleInterface::ADMIN_PANEL => [
        RoleConfig::OPTION_DESC => 'Grants access to the admin panel',
    ],

    RoleInterface::DEVELOPER => [
        RoleConfig::OPTION_DESC     => 'Role for developers',
        RoleConfig::OPTION_INHERITS => [
            RoleInterface::ADMIN_PANEL, // Developer has access to admin panel
            RoleInterface::MODERATOR,
            RoleInterface::LOGIN,       // Developers are always allowed to login
        ],
    ],
];
