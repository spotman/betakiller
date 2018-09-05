<?php
declare(strict_types=1);

namespace BetaKiller\Config;

class GeoMaxMindConfig extends AbstractConfig implements GeoMaxMindConfigInterface
{
    /**
     * @return string
     */
    protected function getConfigRootGroup(): string
    {
        return self::CONFIG_GROUP_NAME;
    }

    /**
     * @return string
     */
    public function getLanguageItemsLocale(): string
    {
        return $this->get(self::PATH_LANGUAGE_ITEMS_LOCALE);
    }

    /**
     * @return array
     */
    public function getLanguagesAliasesLocales(): array
    {
        return (array)$this->get(self::PATH_LANGUAGES_ALIASES_LOCALES);
    }
}
