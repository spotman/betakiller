<?php
declare(strict_types=1);

namespace BetaKiller\Model;

interface CountryInterface
{
    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\CountryInterface
     */
    public function setIsoCode(string $value): CountryInterface;

    /**
     * @return string
     */
    public function getIsoCode(): string;

    /**
     * @param \DateTimeInterface|null $value
     *
     * @return \BetaKiller\Model\CountryInterface
     */
    public function setCreatedAt(?\DateTimeInterface $value = null): CountryInterface;

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt(): \DateTimeImmutable;

    /**
     * @param \BetaKiller\Model\UserInterface $userModel
     *
     * @return \BetaKiller\Model\CountryInterface
     */
    public function setCreatedBy(UserInterface $userModel): CountryInterface;

    /**
     * @return \BetaKiller\Model\UserInterface
     */
    public function getCreatedBy(): UserInterface;

    /**
     * @param \DateTimeInterface|null $value
     *
     * @return \BetaKiller\Model\CountryInterface
     */
    public function setApprovedAt(?\DateTimeInterface $value = null): CountryInterface;

    /**
     * @return \DateTimeImmutable|null
     */
    public function getApprovedAt(): ?\DateTimeImmutable;

    /**
     * @param \BetaKiller\Model\UserInterface $userModel
     *
     * @return \BetaKiller\Model\CountryInterface
     */
    public function setApprovedBy(UserInterface $userModel): CountryInterface;

    /**
     * @return \BetaKiller\Model\User|null
     */
    public function getApprovedBy(): ?UserInterface;

    /**
     * @param bool $value
     *
     * @return \BetaKiller\Model\CountryInterface
     */
    public function setEuStatus(bool $value): CountryInterface;

    /**
     * @return \BetaKiller\Model\CountryInterface
     */
    public function enableEu(): CountryInterface;

    /**
     * @return \BetaKiller\Model\CountryInterface
     */
    public function disableEu(): CountryInterface;

    /**
     * @return bool
     */
    public function getEuStatus(): bool;
}
