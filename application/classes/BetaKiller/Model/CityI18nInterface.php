<?php
declare(strict_types=1);

namespace BetaKiller\Model;

interface CityI18nInterface
{
    /**
     * @param int $value
     *
     * @return \BetaKiller\Model\CityI18nInterface
     */
    public function setCityId(int $value): CityI18nInterface;

    /**
     * @return int
     */
    public function getCityId(): int;

    /**
     * @param int $value
     *
     * @return \BetaKiller\Model\CityI18nInterface
     */
    public function setLanguageId(int $value): CityI18nInterface;

    /**
     * @return int
     */
    public function getLanguageId(): int;

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
