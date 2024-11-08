<?php

declare(strict_types=1);

namespace BetaKiller\Dev;

use DebugBar\DebugBar;
use Psr\Http\Message\ServerRequestInterface;

interface DebugBarFactoryInterface
{
    public function create(ServerRequestInterface $request, string $baseUrl): DebugBar;
}
