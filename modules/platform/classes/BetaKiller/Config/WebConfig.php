<?php

declare(strict_types=1);

namespace BetaKiller\Config;

final class WebConfig extends AbstractConfig implements WebConfigInterface
{
    public const KEY_PIPE           = 'pipe';
    public const KEY_MIDDLEWARES       = 'middlewares';
    public const KEY_NOT_FOUND_HANDLER = 'not_found';

    public const KEY_ROUTES = 'routes';

    public const KEY_GET  = 'get';
    public const KEY_POST = 'post';
    public const KEY_ANY  = 'any';

    protected function getConfigRootGroup(): string
    {
        return 'web';
    }

    public function getPipeMiddlewares(): array
    {
        return $this->getArray([self::KEY_PIPE]);
    }

    /**
     * @inheritDoc
     */
    public function getMiddlewareDependencies(string $fqcn): array
    {
        return $this->getArray([self::KEY_MIDDLEWARES, $fqcn], true);
    }

    public function fetchGetRoutes(): array
    {
        // Reverse so more specific are placed before less specific
        return array_reverse($this->getArray([self::KEY_ROUTES, self::KEY_GET], true));
    }

    public function fetchPostRoutes(): array
    {
        // Reverse so more specific are placed before less specific
        return array_reverse($this->getArray([self::KEY_ROUTES, self::KEY_POST], true));
    }

    public function fetchAnyRoutes(): array
    {
        // Reverse so more specific are placed before less specific
        return array_reverse($this->getArray([self::KEY_ROUTES, self::KEY_ANY], true));
    }

    /**
     * @inheritDoc
     */
    public function getNotFoundHandler(): string
    {
        return $this->getString([self::KEY_NOT_FOUND_HANDLER]);
    }
}
