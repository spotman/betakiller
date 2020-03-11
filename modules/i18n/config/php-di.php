<?php

use BetaKiller\I18n\I18nKeysLoaderInterface;
use BetaKiller\I18n\JsonPluralBagFormatter;
use BetaKiller\I18n\LazyAggregateLoader;
use BetaKiller\I18n\PluralBagFactory;
use BetaKiller\I18n\PluralBagFactoryInterface;
use BetaKiller\I18n\PluralBagFormatterInterface;
use BetaKiller\Repository\LanguageRepository;
use BetaKiller\Repository\LanguageRepositoryInterface;
use BetaKiller\Repository\TranslationKeyRepository;
use BetaKiller\Repository\TranslationKeyRepositoryInterface;
use function DI\autowire;

return [

    'definitions' => [
        I18nKeysLoaderInterface::class => autowire(LazyAggregateLoader::class),

        PluralBagFactoryInterface::class   => autowire(PluralBagFactory::class),
        PluralBagFormatterInterface::class => autowire(JsonPluralBagFormatter::class),

        LanguageRepositoryInterface::class       => autowire(LanguageRepository::class),
        TranslationKeyRepositoryInterface::class => autowire(TranslationKeyRepository::class),
    ],

];
