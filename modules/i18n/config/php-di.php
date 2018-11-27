<?php

use BetaKiller\I18n\I18nKeysLoaderInterface;
use BetaKiller\I18n\JsonPluralBagFormatter;
use BetaKiller\I18n\PluralBagFactory;
use BetaKiller\I18n\PluralBagFactoryInterface;
use BetaKiller\I18n\PluralBagFormatterInterface;
use BetaKiller\Repository\LanguageRepository;
use BetaKiller\Repository\LanguageRepositoryInterface;

return [

    'definitions' => [

        I18nKeysLoaderInterface::class => \DI\autowire(\BetaKiller\I18n\LazyAggregateLoader::class),

        PluralBagFactoryInterface::class   => \DI\autowire(PluralBagFactory::class),
        PluralBagFormatterInterface::class => \DI\autowire(JsonPluralBagFormatter::class),

        LanguageRepositoryInterface::class => \DI\autowire(LanguageRepository::class),
    ],

];
