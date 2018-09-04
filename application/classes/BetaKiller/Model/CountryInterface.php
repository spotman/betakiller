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
     * @param int $value
     *
     * @return \BetaKiller\Model\CountryInterface
     */
    public function setCreatedBy(int $value): CountryInterface;

    /**
     * @return int
     */
    public function getCreatedBy(): int;

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
     * @param int $value
     *
     * @return \BetaKiller\Model\CountryInterface
     */
    public function setApprovedBy(int $value): CountryInterface;

    /**
     * @return int
     */
    public function getApprovedBy(): int;
}
