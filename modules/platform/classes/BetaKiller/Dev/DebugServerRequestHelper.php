<?php
declare(strict_types=1);

namespace BetaKiller\Dev;

use DebugBar\DebugBar;
use Psr\Http\Message\ServerRequestInterface;

class DebugServerRequestHelper
{
    public static function withDebugBar(ServerRequestInterface $request, DebugBar $debugBar): ServerRequestInterface
    {
        return $request->withAttribute(DebugBar::class, $debugBar);
    }

    public static function hasDebugBar(ServerRequestInterface $request): bool
    {
        return (bool)$request->getAttribute(DebugBar::class);
    }

    public static function getDebugBar(ServerRequestInterface $request): DebugBar
    {
        return $request->getAttribute(DebugBar::class);
    }
}
