<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use ORM;

final class NotificationFrequency extends ORM implements NotificationFrequencyInterface
{
    /**
     * Send immediately
     */
    public const FREQ_NOW = 'immediately';

    /**
     * Send once a week
     */
    public const FREQ_OAW = 'oaw';

    /**
     * Send twice a week
     */
    public const FREQ_BW = 'bw';

    /**
     * Send three times a week
     */
    public const FREQ_TIW = 'tiw';

    public const COL_CODENAME = 'codename';

    /**
     * Custom configuration (set table name, configure relations, load_with(), etc)
     */
    protected function configure(): void
    {
        $this->_table_name = 'notification_frequencies';
    }

    /**
     * Returns name of I18n key to proceed
     *
     * @return string
     */
    public function getI18nKeyName(): string
    {
        return 'notification.frequency.'.$this->getCodename();
    }

    /**
     * @return string
     */
    public function getCodename(): string
    {
        return (string)$this->get(self::COL_CODENAME);
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\NotificationFrequencyInterface
     */
    public function setCodename(string $value): NotificationFrequencyInterface
    {
        $this->set(self::COL_CODENAME, $value);

        return $this;
    }

    /**
     * @return bool
     */
    public function isImmediately(): bool
    {
        return $this->getCodename() === self::FREQ_NOW;
    }

    /**
     * @return bool
     */
    public function isThreeTimesAWeek(): bool
    {
        return $this->getCodename() === self::FREQ_TIW;
    }

    /**
     * @return bool
     */
    public function isTwiceAWeek(): bool
    {
        return $this->getCodename() === self::FREQ_BW;
    }

    /**
     * @return bool
     */
    public function isOnceAWeek(): bool
    {
        return $this->getCodename() === self::FREQ_OAW;
    }
}
