<?php
declare(strict_types=1);

namespace BetaKiller\I18n;

use BetaKiller\Config\AbstractConfig;
use BetaKiller\Model\LanguageInterface;

class I18nConfig extends AbstractConfig
{
    public const KEY_LANGUAGES = 'languages';
    public const KEY_LOADERS   = 'loaders';
    public const KEY_FALLBACK  = 'fallback';

    /**
     * @return string[]
     */
    public function getAllowedLanguages(): array
    {
        return (array)$this->get([self::KEY_LANGUAGES]) ?: [LanguageInterface::ISO_EN];
    }

    public function getFallbackLanguage(): string
    {
        return (string)$this->get([self::KEY_FALLBACK], true) ?: LanguageInterface::ISO_EN;
    }

    public function getLoaders(): array
    {
        return (array)$this->get([self::KEY_LOADERS]);
    }

    protected function getConfigRootGroup(): string
    {
        return 'i18n';
    }
}
