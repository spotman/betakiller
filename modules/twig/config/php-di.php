<?php
declare(strict_types=1);

use BetaKiller\Twig\TwigEnvironmentFactory;

return [
    'definitions' => [
        Twig_Environment::class => \DI\factory(function (TwigEnvironmentFactory $factory) {
            return $factory->create();
        }),
    ],
];
