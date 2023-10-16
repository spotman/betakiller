<?php

use BetaKiller\Env\AppEnvInterface;
use BetaKiller\I18n\CachingI18nLoader;
use BetaKiller\I18n\I18nConfig;
use BetaKiller\I18n\I18nConfigInterface;
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
use Psr\SimpleCache\CacheInterface;
use function DI\autowire;
use function DI\factory;

return [

    'definitions' => [
        I18nConfigInterface::class => autowire(I18nConfig::class),

        I18nKeysLoaderInterface::class => factory(static function (
            AppEnvInterface         $appEnv,
            LazyAggregateI18nLoader $loader,
            CacheInterface          $cache
        ) {
            // Prevent caching in dev mode
            if ($appEnv->inDevelopmentMode()) {
                return $loader;
            }

            return new CachingI18nLoader($loader, $cache);
        }),

        PluralBagFactoryInterface::class   => autowire(PluralBagFactory::class),
        PluralBagFormatterInterface::class => autowire(JsonPluralBagFormatter::class),

        LanguageRepositoryInterface::class       => autowire(LanguageRepository::class),
        TranslationKeyRepositoryInterface::class => autowire(TranslationKeyRepository::class),
    ],

];
