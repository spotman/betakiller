<?php
declare(strict_types=1);

namespace BetaKiller\Model;

interface CountryI18nInterface
{
    /**
     * @param \BetaKiller\Model\CountryInterface $countryModel
     *
     * @return \BetaKiller\Model\CountryI18nInterface
     */
    public function setCountry(CountryInterface $countryModel): CountryI18nInterface;

    /**
     * @return Country
     */
    public function getCountry(): CountryInterface;

    /**
     * @param \BetaKiller\Model\LanguageInterface $languageModel
     *
     * @return \BetaKiller\Model\CountryI18nInterface
     */
    public function setLanguage(LanguageInterface $languageModel): CountryI18nInterface;

    /**
     * @return \BetaKiller\Model\LanguageInterface
     */
    public function getLanguage(): LanguageInterface;

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\CountryI18nInterface
     */
    public function setValue(string $value): CountryI18nInterface;

    /**
     * @return string
     */
    public function getValue(): string;
}
