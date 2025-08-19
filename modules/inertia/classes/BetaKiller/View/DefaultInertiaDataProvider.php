<?php

declare(strict_types=1);

namespace BetaKiller\View;

use BetaKiller\Env\AppEnvInterface;
use Cherif\InertiaPsr15\Service\InertiaInterface;
use Psr\Http\Message\ServerRequestInterface;

readonly class DefaultInertiaDataProvider implements InertiaDataProviderInterface
{
    public function __construct(private AppEnvInterface $appEnv)
    {
    }

    public function injectSharedData(ServerRequestInterface $request, InertiaInterface $inertia): void
    {
        $inertia
            ->version($this->appEnv->getRevisionKey());
    }
}
