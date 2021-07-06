<?php
declare(strict_types=1);

namespace BetaKiller\Config;

class RoleConfig
{
    /**
     * Filename to search for
     */
    public const CONFIG_GROUP_NAME = 'roles';

    /**
     * Role config attributes
     */
    public const OPTION_DESC     = 'desc';
    public const OPTION_INHERITS = 'inherits';
}
