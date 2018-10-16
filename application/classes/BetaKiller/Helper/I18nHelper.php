<?php
namespace BetaKiller\Helper;

use BetaKiller\Exception;
use BetaKiller\I18n\I18nFacade;

class I18nHelper
{
    private const KEY_REGEX = '/^[a-z0-9_]+(?:[\.]{1}[a-z0-9_]+)+$/m';

    /**
     * @var string
     */
    private $lang;

    /**
     * @var \BetaKiller\I18n\I18nFacade
     */
    private $facade;

    /**
     * I18nHelper constructor.
     *
     * @param \BetaKiller\I18n\I18nFacade $facade
     */
    public function __construct(I18nFacade $facade)
    {
        $this->facade = $facade;
    }

    public function getDefaultLanguage(): string
    {
        return $this->facade->getDefaultLanguage();
    }

    public function getAllowedLanguages(): array
    {
        return $this->facade->getAllowedLanguages();
    }

    public function getLang(): string
    {
        return $this->lang;
    }

    public function setLang(string $value): void
    {
        if (!$this->facade->hasLanguage($value)) {
            throw new Exception('Unknown language :lang, only these are allowed: :allowed', [
                ':lang'    => $value,
                ':allowed' => implode(', ', $this->getAllowedLanguages()),
            ]);
        }

        $this->lang = $value;

        // Set I18n lang
        \I18n::lang($value);
    }

    public function getLocale(): string
    {
        $lang = $this->lang ?: $this->getDefaultLanguage();

        return $this->facade->getLanguageLocale($lang);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function isI18nKey(string $key): bool
    {
        return (bool)preg_match(self::KEY_REGEX, $key);
    }

    public function translate(string $key, array $values = null, string $lang = null): string
    {
        return $this->facade->translate($lang ?: $this->lang, $key, $values);
    }
}
