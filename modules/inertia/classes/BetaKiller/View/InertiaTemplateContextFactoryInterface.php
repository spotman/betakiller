<?php

declare(strict_types=1);

namespace BetaKiller\View;

use Psr\Http\Message\ServerRequestInterface;

interface InertiaTemplateContextFactoryInterface
{
    public function fromRequest(ServerRequestInterface $request): TemplateContext;
}
