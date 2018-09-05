<?php
declare(strict_types=1);

namespace BetaKiller\Config;

interface GeoMaxMindConfigInterface
{
    public const  CONFIG_GROUP_NAME              = 'geo';
    private const PATH_GROUP                     = ['maxmind'];
    public const  PATH_LANGUAGE_ITEMS_LOCALE     = self::PATH_GROUP + ['languages', 'itemsLocale'];
    public const  PATH_LANGUAGES_ALIASES_LOCALES = self::PATH_GROUP + ['languages', 'aliasesLocales'];

    /**
     * @return string
     */
    public function getLanguageItemsLocale(): string;

    /**
     * @return array
     */
    public function getLanguagesAliasesLocales(): array;
}
