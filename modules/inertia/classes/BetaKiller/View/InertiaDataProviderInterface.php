<?php

declare(strict_types=1);

namespace BetaKiller\View;

use Cherif\InertiaPsr15\Service\InertiaInterface;
use Psr\Http\Message\ServerRequestInterface;

interface InertiaDataProviderInterface
{
    public function injectSharedData(ServerRequestInterface $request, InertiaInterface $inertia): void;
}
