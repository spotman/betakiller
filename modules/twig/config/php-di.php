<?php
declare(strict_types=1);

use BetaKiller\Twig\TwigEnvironmentFactory;
use BetaKiller\Widget\WidgetFacade;

return [
    'definitions' => [
        Twig_Environment::class => \DI\factory(function(TwigEnvironmentFactory $factory) {
            return $factory->create();
        }),

        // Lazy injection coz circular dependency TwigExtension => WidgetFacade => TwigExtension
        WidgetFacade::class => \DI\autowire(WidgetFacade::class)->lazy(),
    ]
];
