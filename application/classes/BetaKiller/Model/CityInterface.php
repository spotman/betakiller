<?php
declare(strict_types=1);

namespace BetaKiller\Model;

interface CityInterface
{
    /**
     * @param int $value
     *
     * @return \BetaKiller\Model\CityInterface
     */
    public function setCountryId(int $value): CityInterface;

    /**
     * @return int
     */
    public function getCountryId(): int;

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\CityInterface
     */
    public function setName(string $value): CityInterface;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param \DateTimeInterface|null $value
     *
     * @return \BetaKiller\Model\CityInterface
     */
    public function setCreatedAt(?\DateTimeInterface $value = null): CityInterface;

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt(): \DateTimeImmutable;

    /**
     * @param int $value
     *
     * @return \BetaKiller\Model\CityInterface
     */
    public function setCreatedBy(int $value): CityInterface;

    /**
     * @return int
     */
    public function getCreatedBy(): int;

    /**
     * @param \DateTimeInterface|null $value
     *
     * @return \BetaKiller\Model\CityInterface
     */
    public function setApprovedAt(?\DateTimeInterface $value = null): CityInterface;

    /**
     * @return \DateTimeImmutable|null
     */
    public function getApprovedAt(): ?\DateTimeImmutable;

    /**
     * @param int $value
     *
     * @return \BetaKiller\Model\CityInterface
     */
    public function setApprovedBy(int $value): CityInterface;

    /**
     * @return int
     */
    public function getApprovedBy(): int;

    /**
     * @return \BetaKiller\Model\CountryInterface
     */
    public function getCountry(): CountryInterface;
}
