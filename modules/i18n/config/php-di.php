<?php

use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\I18n\CachingI18nLoader;
use BetaKiller\I18n\I18nKeysLoaderInterface;
use BetaKiller\I18n\JsonPluralBagFormatter;
use BetaKiller\I18n\LazyAggregateI18nLoader;
use BetaKiller\I18n\PluralBagFactory;
use BetaKiller\I18n\PluralBagFactoryInterface;
use BetaKiller\I18n\PluralBagFormatterInterface;
use BetaKiller\Repository\LanguageRepository;
use BetaKiller\Repository\LanguageRepositoryInterface;
use BetaKiller\Repository\TranslationKeyRepository;
use BetaKiller\Repository\TranslationKeyRepositoryInterface;
use function DI\autowire;
use function DI\factory;

return [

    'definitions' => [
        I18nKeysLoaderInterface::class => factory(static function (
            AppEnvInterface $appEnv,
            LazyAggregateI18nLoader $loader
        ) {
            // Prevent caching in dev mode
            if ($appEnv->inDevelopmentMode()) {
                return $loader;
            }

            $path = implode(DIRECTORY_SEPARATOR, [
                $appEnv->getAppRootPath(),
                'cache',
                'i18n',
            ]);

            return new CachingI18nLoader($loader, $path);
        }),

        PluralBagFactoryInterface::class   => autowire(PluralBagFactory::class),
        PluralBagFormatterInterface::class => autowire(JsonPluralBagFormatter::class),

        LanguageRepositoryInterface::class       => autowire(LanguageRepository::class),
        TranslationKeyRepositoryInterface::class => autowire(TranslationKeyRepository::class),
    ],

];
