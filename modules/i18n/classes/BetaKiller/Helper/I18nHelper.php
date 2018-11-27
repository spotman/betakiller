<?php
namespace BetaKiller\Helper;

use BetaKiller\Exception;
use BetaKiller\I18n\I18nFacade;
use BetaKiller\Model\HasI18nKeyNameInterface;
use BetaKiller\Model\I18nKeyInterface;

class I18nHelper
{
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

    public function getDefaultLanguageName(): string
    {
        return $this->facade->getDefaultLanguageName();
    }

    /**
     * @return string[]
     */
    public function getAllowedLanguagesNames(): array
    {
        return $this->facade->getAllowedLanguagesNames();
    }

    public function getLang(): string
    {
        return $this->lang;
    }

    public function setLang(string $value): void
    {
        // Normalize the language
        $value = strtolower(str_replace([' ', '_'], '-', $value));

        if (!$this->facade->hasLanguage($value)) {
            throw new Exception('Unknown language :lang, only these are allowed: :allowed', [
                ':lang'    => $value,
                ':allowed' => implode(', ', $this->getAllowedLanguagesNames()),
            ]);
        }

        $this->lang = $value;
    }

    public function getLocale(): string
    {
        $langName = $this->lang ?: $this->getDefaultLanguageName();

        return $this->facade->getLanguageByName($langName)->getLocale();
    }

    public function translateHasKeyName(HasI18nKeyNameInterface $hasKey, string $lang = null): string
    {
        return $this->facade->translateHasKeyName($lang ?: $this->lang, $hasKey);
    }

    public function translateKeyName(string $key, array $values = null, string $lang = null): string
    {
        return $this->facade->translateKeyName($lang ?: $this->lang, $key, $values);
    }

    public function translateKey(I18nKeyInterface $model, array $values = null, string $lang = null): string
    {
        return $this->facade->translateKey($lang, $model, $values);
    }

    public function pluralizeKeyName(string $key, $form, array $values = null, string $lang = null): string
    {
        return $this->facade->pluralizeKeyName($lang ?: $this->lang, $key, $form, $values);
    }
}
