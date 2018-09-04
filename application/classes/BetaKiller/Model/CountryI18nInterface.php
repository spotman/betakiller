<?php
declare(strict_types=1);

namespace BetaKiller\Model;

interface CountryI18nInterface
{
    /**
     * @param int $value
     *
     * @return \BetaKiller\Model\CountryI18nInterface
     */
    public function setCountryId(int $value): CountryI18nInterface;

    /**
     * @return int
     */
    public function getCountryId(): int;

    /**
     * @param int $value
     *
     * @return \BetaKiller\Model\CountryI18nInterface
     */
    public function setLanguageId(int $value): CountryI18nInterface;

    /**
     * @return int
     */
    public function getLanguageId(): int;

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
