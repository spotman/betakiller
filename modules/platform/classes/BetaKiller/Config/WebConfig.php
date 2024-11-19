<?php
declare(strict_types=1);

namespace BetaKiller\Config;

final class WebConfig extends AbstractConfig implements WebConfigInterface
{
    public const KEY_MIDDLEWARES = 'middlewares';

    public const KEY_ROUTES = 'routes';

    public const KEY_GET  = 'get';
    public const KEY_POST = 'post';
    public const KEY_ANY  = 'any';

    protected function getConfigRootGroup(): string
    {
        return 'web';
    }

    public function getMiddlewares(): array
    {
        return $this->getArray([self::KEY_MIDDLEWARES]);
    }

    public function fetchGetRoutes(): array
    {
        // Reverse so more specific are placed before less specific
        return array_reverse($this->getArray([self::KEY_ROUTES, self::KEY_GET]));
    }

    public function fetchPostRoutes(): array
    {
        // Reverse so more specific are placed before less specific
        return array_reverse($this->getArray([self::KEY_ROUTES, self::KEY_POST]));
    }

    public function fetchAnyRoutes(): array
    {
        // Reverse so more specific are placed before less specific
        return array_reverse($this->getArray([self::KEY_ROUTES, self::KEY_ANY]));
    }
}
