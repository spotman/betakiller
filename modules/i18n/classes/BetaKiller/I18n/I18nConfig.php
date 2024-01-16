<?php
declare(strict_types=1);

namespace BetaKiller\I18n;

use BetaKiller\Config\AbstractConfig;
use BetaKiller\Model\LanguageInterface;

class I18nConfig extends AbstractConfig implements I18nConfigInterface
{
    public const KEY_LANGUAGES          = 'languages';
    public const KEY_LOADERS            = 'loaders';
    public const KEY_FALLBACK           = 'fallback';
    public const KEY_FALLBACK_ONLY_KEYS = 'fallback_only_keys';

    /**
     * @inheritDoc
     */
    public function getAllowedLanguages(): array
    {
        return (array)$this->get([self::KEY_LANGUAGES]) ?: [LanguageInterface::ISO_EN];
    }

    /**
     * @inheritDoc
     */
    public function getFallbackLanguage(): ?string
    {
        return $this->get([self::KEY_FALLBACK], true);
    }

    /**
     * @inheritDoc
     */
    public function getLoaders(): array
    {
        return (array)$this->get([self::KEY_LOADERS]);
    }

    /**
     * @inheritDoc
     */
    public function getFallbackOnlyKeys(): array
    {
        return (array)$this->get([self::KEY_FALLBACK_ONLY_KEYS]);
    }

    protected function getConfigRootGroup(): string
    {
        return 'i18n';
    }
}
