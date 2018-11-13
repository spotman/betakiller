<?php
namespace BetaKiller\Helper;

use BetaKiller\Exception;
use BetaKiller\I18n\I18nFacade;
use BetaKiller\Model\I18nKeyModelInterface;

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
        $lang = $this->lang ?: $this->getDefaultLanguageName();

        return $this->facade->getLanguageLocale($lang);
    }

    public function translate(string $key, array $values = null, string $lang = null): string
    {
        return $this->facade->translate($lang ?: $this->lang, $key, $values);
    }

    public function translateKey(I18nKeyModelInterface $model, array $values = null, string $lang = null): string
    {
        return $this->translate($model->getI18nKey(), $values, $lang);
    }

    public function pluralize(string $key, $form, array $values = null, string $lang = null): string
    {
        return $this->facade->pluralize($lang ?: $this->lang, $key, $form, $values);
    }
}
