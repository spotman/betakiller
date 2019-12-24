<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use BetaKiller\Exception\DomainException;
use Cron\CronExpression;
use DateTimeImmutable;
use DateTimeInterface;
use ORM;

final class NotificationFrequency extends ORM implements NotificationFrequencyInterface
{
    /**
     * Send immediately
     */
    public const FREQ_IMMEDIATELY = 'immediately';

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

    private const CRON_EXPRESSIONS = [
        self::FREQ_OAW => '30 9 * * MON',
        self::FREQ_BW  => '20 9 * * TUE,THU',
        self::FREQ_TIW => '10 9 * * MON,WED,FRI',
    ];

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
        return $this->getCodename() === self::FREQ_IMMEDIATELY;
    }

    /**
     * @return \DateTimeInterface
     */
    public function calculateSchedule(): DateTimeInterface
    {
        $expr = $this->getCronExpression();

        $now = new DateTimeImmutable;

        if (!$expr) {
            return $now;
        }

        return $expr->getNextRunDate($now, 0, true);
    }

    /**
     * @return bool
     */
    public function isDue(): bool
    {
        if ($this->isImmediately()) {
            throw new DomainException('Immediate notification frequency cannot be due');
        }

        return $this->getCronExpression()->isDue();
    }

    private function getCronExpression(): CronExpression
    {
        if ($this->isImmediately()) {
            throw new DomainException('Immediate notification frequency cannot have CRON expression');
        }

        $value = self::CRON_EXPRESSIONS[$this->getCodename()] ?? null;

        if (!$value) {
            throw new DomainException('CRON expr is missing for ":name" Notification frequency', [
                ':name' => $this->getCodename(),
            ]);
        }

        return CronExpression::factory($value);
    }
}
