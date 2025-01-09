<?php

namespace BetaKiller\IFace\Cache;

use BetaKiller\IFace\IFaceInterface;
use Psr\Http\Message\ServerRequestInterface;

interface IFaceCacheInterface
{
    public function clearModelCache(): void;

    public function clearCache(): void;

    public function process(IFaceInterface $iface, ServerRequestInterface $request): void;

    public function disable(): void;
}
