<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use DateTimeInterface;

interface NotificationFrequencyInterface extends AbstractEntityInterface, HasI18nKeyNameInterface
{
    /**
     * @return string
     */
    public function getCodename(): string;

    /**
     * @param string $value
     *
     * @return NotificationFrequencyInterface
     */
    public function setCodename(string $value): NotificationFrequencyInterface;

    /**
     * @return \DateTimeInterface
     */
    public function calculateSchedule(): DateTimeInterface;

    /**
     * @return bool
     */
    public function isDue(): bool;

    /**
     * @return bool
     */
    public function isImmediately(): bool;
}
