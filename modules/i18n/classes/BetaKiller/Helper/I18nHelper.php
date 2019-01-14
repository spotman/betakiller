<?php
namespace BetaKiller\Helper;

use BetaKiller\Exception;
use BetaKiller\I18n\I18nFacade;
use BetaKiller\Model\HasI18nKeyNameInterface;
use BetaKiller\Model\I18nKeyInterface;
use BetaKiller\Model\LanguageInterface;

class I18nHelper
{
    /**
     * @var LanguageInterface
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

    public function getLang(): LanguageInterface
    {
        return $this->lang;
    }

    public function setLang(LanguageInterface $value): void
    {
        if (!$this->facade->hasLanguage($value->getIsoCode())) {
            throw new Exception('Unknown language ":lang", only these are allowed: :allowed', [
                ':lang'    => $value,
                ':allowed' => implode(', ', $this->facade->getAllowedLanguagesIsoCodes()),
            ]);
        }

        $this->lang = $value;
    }

    public function translateHasKeyName(HasI18nKeyNameInterface $hasKey): string
    {
        return $this->facade->translateHasKeyName($this->lang, $hasKey);
    }

    public function translateKeyName(string $key, array $values = null): string
    {
        return $this->facade->translateKeyName($this->lang, $key, $values);
    }

    public function translateKey(I18nKeyInterface $model, array $values = null): string
    {
        return $this->facade->translateKey($this->lang, $model, $values);
    }

    public function pluralizeKeyName(string $key, $form, array $values = null): string
    {
        return $this->facade->pluralizeKeyName($this->lang, $key, $form, $values);
    }
}
