<?php

declare(strict_types=1);

namespace BetaKiller\View;

use Psr\Http\Message\ServerRequestInterface;

final readonly class DefaultInertiaTemplateContextFactory implements InertiaTemplateContextFactoryInterface
{
    public function __construct(private TemplateContextFactory $factory)
    {
    }

    public function fromRequest(ServerRequestInterface $request): TemplateContext
    {
        return $this->factory->fromRequest($request)
            ->wrapInHtml5();
    }
}
