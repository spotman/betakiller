<?php
declare(strict_types=1);

namespace BetaKiller\Model;

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
     * @return bool
     */
    public function isImmediately(): bool;

    /**
     * @return bool
     */
    public function isThreeTimesAWeek(): bool;

    /**
     * @return bool
     */
    public function isTwiceAWeek(): bool;

    /**
     * @return bool
     */
    public function isOnceAWeek(): bool;
}
