<?php
declare(strict_types=1);

use BetaKiller\Config\RoleConfig;
use BetaKiller\I18n\I18nFacade;
use BetaKiller\Model\RoleInterface;

return [
    I18nFacade::ROLE_TRANSLATOR => [
        RoleConfig::OPTION_DESC     => 'Grants access to i18n keys translation',
        RoleConfig::OPTION_INHERITS => [
            RoleInterface::ADMIN_PANEL, // Translation is made through admin panel
        ],
    ],

    RoleInterface::DEVELOPER => [
        RoleConfig::OPTION_INHERITS => [
            I18nFacade::ROLE_TRANSLATOR,
        ],
    ],
];
