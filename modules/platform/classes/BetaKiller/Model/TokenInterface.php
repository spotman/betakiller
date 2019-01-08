<?php
declare(strict_types=1);

namespace BetaKiller\Model;

interface TokenInterface extends DispatchableEntityInterface
{
    /**
     * @param \BetaKiller\Model\UserInterface $userModel
     *
     * @return \BetaKiller\Model\TokenInterface
     */
    public function setUser(UserInterface $userModel): TokenInterface;

    /**
     * @return \BetaKiller\Model\UserInterface
     */
    public function getUser(): UserInterface;

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\TokenInterface
     */
    public function setValue(string $value): TokenInterface;

    /**
     * @return string
     */
    public function getValue(): string;

    /**
     * @param \DateTimeImmutable $value
     *
     * @return \BetaKiller\Model\TokenInterface
     */
    public function setCreatedAt(\DateTimeImmutable $value): TokenInterface;

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt(): \DateTimeImmutable;

    /**
     * @param \DateTimeImmutable $value
     *
     * @return \BetaKiller\Model\TokenInterface
     */
    public function setEndingAt(\DateTimeImmutable $value): TokenInterface;

    /**
     * @return \DateTimeImmutable
     */
    public function getEndingAt(): \DateTimeImmutable;

    /**
     * @param \DateTimeImmutable $value
     *
     * @return \BetaKiller\Model\TokenInterface
     */
    public function setUsedAt(\DateTimeImmutable $value): TokenInterface;

    /**
     * @return \DateTimeImmutable|null
     */
    public function getUsedAt(): ?\DateTimeImmutable;

    /**
     * @return bool
     */
    public function isUsed(): bool;

    /**
     * @return bool
     */
    public function isActive(): bool;
}
