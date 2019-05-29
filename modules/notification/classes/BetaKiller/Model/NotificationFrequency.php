<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use Cron\CronExpression;
use DateTimeImmutable;
use DateTimeInterface;
use ORM;

class NotificationFrequency extends ORM implements NotificationFrequencyInterface
{
    public const COL_CODENAME = 'codename';
    public const COL_CRON     = 'cron_expression';

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

    private function getCronExpression(): ?CronExpression
    {
        $value = (string)$this->get(self::COL_CRON);

        return $value
            ? CronExpression::factory($value)
            : null;
    }
}
