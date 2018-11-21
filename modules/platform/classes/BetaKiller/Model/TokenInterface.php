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
    public function setEndingAt(\DateTimeImmutable $value): TokenInterface;

    /**
     * @return \DateTimeImmutable
     */
    public function getEndingAt(): \DateTimeImmutable;

    /**
     * @return bool
     */
    public function isActive(): bool;
}
