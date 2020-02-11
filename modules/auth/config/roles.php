<?php
declare(strict_types=1);

use BetaKiller\Auth\RoleConfig;
use BetaKiller\Model\RoleInterface;

return [
    // Separate role for guests (do not inherit in other roles)
    RoleInterface::GUEST => [
        RoleConfig::OPTION_DESC => 'All guests and unauthorized users',
    ],

    RoleInterface::LOGIN => [
        RoleConfig::OPTION_DESC => 'Any user allowed to sign-in',
    ],

    RoleInterface::ADMIN_PANEL => [
        RoleConfig::OPTION_DESC     => 'Grants access to the admin panel',
        RoleConfig::OPTION_INHERITS => [
            RoleInterface::LOGIN,       // Admins are allowed to login
        ],
    ],

    RoleInterface::ROLE_USER_MANAGEMENT => [
        RoleConfig::OPTION_DESC     => 'Grants access to user management',
        RoleConfig::OPTION_INHERITS => [
            // User management is done via admin panel
            RoleInterface::ADMIN_PANEL,
        ],
    ],

    RoleInterface::DEVELOPER => [
        RoleConfig::OPTION_DESC     => 'Developer',
        RoleConfig::OPTION_INHERITS => [
            RoleInterface::ADMIN_PANEL, // Developer has access to admin panel
        ],
    ],

    RoleInterface::CLI => [
        RoleConfig::OPTION_DESC     => 'Console task runner',
        RoleConfig::OPTION_INHERITS => [
            // Allow everything for simplicity
            RoleInterface::DEVELOPER,
        ],
    ],
];
