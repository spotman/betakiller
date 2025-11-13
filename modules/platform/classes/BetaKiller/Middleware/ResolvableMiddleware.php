<?php

declare(strict_types=1);

namespace BetaKiller\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final readonly class ResolvableMiddleware implements MiddlewareInterface
{
    public function __construct(private ResolvableMiddlewareFactory $factory, private string $fqcn)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->factory->createFor($this->fqcn)->process($request, $handler);
    }
}
