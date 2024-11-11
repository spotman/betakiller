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
        // Fetch actual session
        $session = ServerRequestHelper::getSession($request);

        // Read global debug setting
        // Prevent displaying DebugBar in prod mode (even if global debug is on)
        // Prevent guests
        $showBar = $this->appEnv->isDebugEnabled()
            && !$this->appEnv->inProductionMode()
            && SessionHelper::hasUserID($session);

        // TODO Display bar only for User with "show-debug-bar" role

        // Detect debug mode enabled for session
        // Do not fetch User here to allow lazy loading
        $showBar = $showBar || SessionHelper::isDebugEnabled($session);

        // Always show in dev mode
        return $showBar || $this->appEnv->inDevelopmentMode();
    }
}
