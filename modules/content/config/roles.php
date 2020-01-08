<?php
declare(strict_types=1);

use BetaKiller\Auth\RoleConfig;
use BetaKiller\Content\Content;
use BetaKiller\Model\RoleInterface;

return [
    Content::ROLE_CONTENT_MODERATOR => [
        RoleConfig::OPTION_DESC => 'Content moderator',
    ],

    Content::ROLE_WRITER => [
        RoleConfig::OPTION_DESC => 'Writer (content creator)',
    ],

    RoleInterface::DEVELOPER => [
        RoleConfig::OPTION_INHERITS => [
            Content::ROLE_WRITER,
            Content::ROLE_CONTENT_MODERATOR,
        ],
    ],
];
