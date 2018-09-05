<?php
declare(strict_types=1);

namespace BetaKiller\Model;

interface CityI18nInterface
{
    /**
     * @param \BetaKiller\Model\CityInterface $cityModel
     *
     * @return \BetaKiller\Model\CityI18nInterface
     */
    public function setCity(CityInterface $cityModel): CityI18nInterface;

    /**
     * @return \BetaKiller\Model\CityInterface
     */
    public function getCity(): CityInterface;

    /**
     * @param \BetaKiller\Model\LanguageInterface $languageModel
     *
     * @return \BetaKiller\Model\CityI18nInterface
     */
    public function setLanguage(LanguageInterface $languageModel): CityI18nInterface;

    /**
     * @return \BetaKiller\Model\LanguageInterface
     */
    public function getLanguage(): LanguageInterface;

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\CityI18nInterface
     */
    public function setValue(string $value): CityI18nInterface;

    /**
     * @return string
     */
    public function getValue(): string;
}
