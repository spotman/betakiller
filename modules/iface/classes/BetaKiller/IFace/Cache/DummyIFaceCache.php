<?php

declare(strict_types=1);

namespace BetaKiller\IFace\Cache;

use BetaKiller\IFace\IFaceInterface;
use Psr\Http\Message\ServerRequestInterface;

final class DummyIFaceCache implements IFaceCacheInterface
{
    public function clearModelCache(): void
    {
        // Nothing here
    }

    public function clearCache(): void
    {
        // Nothing here
    }

    public function process(IFaceInterface $iface, ServerRequestInterface $request): void
    {
        // Nothing here
    }

    public function disable(): void
    {
        // Nothing here
    }
}
