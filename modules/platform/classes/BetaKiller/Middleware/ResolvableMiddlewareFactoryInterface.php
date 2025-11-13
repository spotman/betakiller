<?php

declare(strict_types=1);

namespace BetaKiller\Middleware;

use Psr\Http\Server\MiddlewareInterface;

interface ResolvableMiddlewareFactoryInterface
{
    public function createFor(string $fqcn): MiddlewareInterface;
}
