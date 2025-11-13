<?php

declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Config\WebConfigInterface;
use Mezzio\MiddlewareFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;

final readonly class ResolvableMiddlewareFactory implements ResolvableMiddlewareFactoryInterface
{
    public function __construct(private MiddlewareFactoryInterface $middlewareFactory, private WebConfigInterface $config)
    {
    }

    public function createFor(string $fqcn): MiddlewareInterface
    {
        $pipe = $this->fromCache($fqcn);

        if ($pipe === null) {
            $pipe = $this->resolvePipe($fqcn);
            $this->toCache($fqcn, $pipe);
        }

        return $this->middlewareFactory->pipeline($pipe);
    }

    private function fromCache(string $fqcn): ?array
    {
        // TODO APCu cache
        return null;
    }

    private function toCache(string $fqcn, array $pipe): void
    {
        // TODO APCu cache
    }

    private function resolvePipe(string $fqcn): array
    {
        $dependencies = $this->config->getMiddlewareDependencies($fqcn);

        // TODO Fetch all dependencies from config recursively
        // TODO Resolve dependencies tree

        // Target middleware last
        $dependencies[] = $fqcn;

        return $dependencies;
    }
}
