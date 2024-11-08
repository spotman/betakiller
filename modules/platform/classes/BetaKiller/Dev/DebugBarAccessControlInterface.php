<?php

declare(strict_types=1);

namespace BetaKiller\Dev;

use Psr\Http\Message\ServerRequestInterface;

interface DebugBarAccessControlInterface
{
    public function isAllowedFor(ServerRequestInterface $request): bool;
}
