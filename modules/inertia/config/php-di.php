<?php

declare(strict_types=1);

use BetaKiller\View\DefaultInertiaTemplateContextFactory;
use BetaKiller\View\IFaceRendererInterface;
use BetaKiller\View\InertiaFactory;
use BetaKiller\View\InertiaIFaceRenderer;
use BetaKiller\View\InertiaTemplateContextFactoryInterface;
use Cherif\InertiaPsr15\Service\InertiaFactoryInterface;

use function DI\autowire;

return [
    'definitions' => [
        // Inertia.js
        InertiaFactoryInterface::class                => autowire(InertiaFactory::class),
        IFaceRendererInterface::class                 => autowire(InertiaIFaceRenderer::class),
        InertiaTemplateContextFactoryInterface::class => autowire(DefaultInertiaTemplateContextFactory::class),
    ],
];
