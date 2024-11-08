<?php

declare(strict_types=1);

namespace BetaKiller\Dev;

use BetaKiller\Env\AppEnvInterface;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\SessionHelper;
use Psr\Http\Message\ServerRequestInterface;

readonly class DebugBarAccessControl implements DebugBarAccessControlInterface
{
    public function __construct(private AppEnvInterface $appEnv)
    {
    }

    public function isAllowedFor(ServerRequestInterface $request): bool
    {
        // Read global debug setting
        $showBar = $this->appEnv->isDebugEnabled();

        // Prevent displaying DebugBar in prod mode (even if global debug is on)
        if ($this->appEnv->inProductionMode()) {
            $showBar = false;
        }

        // Fetch actual session
        $session = ServerRequestHelper::getSession($request);

        // Detect debug mode enabled for session
        // Do not fetch User here to allow lazy loading
        if (SessionHelper::isDebugEnabled($session)) {
            $showBar = true;
        }

        // TODO Display bar only for User with "show-debug-bar" role

        return $showBar;
    }
}
