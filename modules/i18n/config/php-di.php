<?php

use BetaKiller\I18n\JsonPluralBagFormatter;
use BetaKiller\I18n\LoaderFactory;
use BetaKiller\I18n\LoaderInterface;
use BetaKiller\I18n\PluralBagFactory;
use BetaKiller\I18n\PluralBagFactoryInterface;
use BetaKiller\I18n\PluralBagFormatterInterface;
use BetaKiller\I18n\Translator;
use BetaKiller\I18n\TranslatorInterface;

return [

    'definitions' => [

        LoaderInterface::class => \DI\factory(function (LoaderFactory $factory) {
            return $factory->create();
        }),

        TranslatorInterface::class         => \DI\autowire(Translator::class),
        PluralBagFactoryInterface::class   => \DI\autowire(PluralBagFactory::class),
        PluralBagFormatterInterface::class => \DI\autowire(JsonPluralBagFormatter::class),

    ],

];
