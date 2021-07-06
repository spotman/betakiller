<?php
declare(strict_types=1);

use BetaKiller\Config\RoleConfig;
use BetaKiller\Helper\ContentHelper;
use BetaKiller\Model\RoleInterface;

return [
    ContentHelper::ROLE_CONTENT_MODERATOR => [
        RoleConfig::OPTION_DESC => 'Content moderator',
    ],

    ContentHelper::ROLE_WRITER => [
        RoleConfig::OPTION_DESC => 'Writer (content creator)',
    ],

    RoleInterface::DEVELOPER => [
        RoleConfig::OPTION_INHERITS => [
            ContentHelper::ROLE_WRITER,
            ContentHelper::ROLE_CONTENT_MODERATOR,
        ],
    ],
];
