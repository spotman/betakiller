<?php
declare(strict_types=1);

namespace BetaKiller\Model;

interface UserStatusInterface
{
    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\UserStatusInterface
     */
    public function setCodename(string $value): UserStatusInterface;

    /**
     * @return string
     */
    public function getCodename(): string;

    /**
     * @return string
     */
    public function getLabelI18nKey(): string;

    /**
     * @param string $value
     *
     * @return bool
     */
    public function isStatus(string $value): bool;

    /**
     * @return bool
     */
    public function isCreated(): bool;

    /**
     * @return bool
     */
    public function isApproved(): bool;

    /**
     * @return bool
     */
    public function isVerified(): bool;

    /**
     * @return bool
     */
    public function isBlocked(): bool;
}