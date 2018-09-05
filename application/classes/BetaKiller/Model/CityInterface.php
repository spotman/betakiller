<?php
declare(strict_types=1);

namespace BetaKiller\Model;

interface CityInterface
{
    /**
     * @param \BetaKiller\Model\CountryInterface $countryModel
     *
     * @return \BetaKiller\Model\CityInterface
     */
    public function setCountry(CountryInterface $countryModel): CityInterface;

    /**
     * @return \BetaKiller\Model\CountryInterface
     */
    public function getCountry(): CountryInterface;

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
     * @param \BetaKiller\Model\UserInterface $userModel
     *
     * @return \BetaKiller\Model\CityInterface
     */
    public function setCreatedBy(UserInterface $userModel): CityInterface;

    /**
     * @return \BetaKiller\Model\User
     */
    public function getCreatedBy(): UserInterface;

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
     * @param \BetaKiller\Model\UserInterface $userModel
     *
     * @return \BetaKiller\Model\CityInterface
     */
    public function setApprovedBy(UserInterface $userModel): CityInterface;

    /**
     * @return \BetaKiller\Model\UserInterface|null
     */
    public function getApprovedBy(): ?UserInterface;

    /**
     * @param int $value
     *
     * @return \BetaKiller\Model\CityInterface
     */
    public function setMaxmindId(int $value): CityInterface;

    /**
     * @return int
     */
    public function getMaxmindId(): int;
}
