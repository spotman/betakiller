<?php

declare(strict_types=1);

namespace BetaKiller\View;

use Cherif\InertiaPsr15\Service\Inertia;
use Cherif\InertiaPsr15\Service\InertiaFactoryInterface;
use Cherif\InertiaPsr15\Service\InertiaInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

final readonly class InertiaFactory implements InertiaFactoryInterface
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private StreamFactoryInterface $streamFactory,
        private ViewFactoryInterface $viewFactory,
        private LayoutViewInterface $layoutView,
        private InertiaTemplateContextFactoryInterface $contextFactory
    ) {
    }

    public function fromRequest(ServerRequestInterface $request): InertiaInterface
    {
        $rootProvider = new InertiaRootViewProvider($this->viewFactory, $this->layoutView, $this->contextFactory, $request);

        return new Inertia(
            $request,
            $this->responseFactory,
            $this->streamFactory,
            $rootProvider
        );
    }
}
