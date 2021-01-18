<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use BetaKiller\Exception\DomainException;
use BetaKiller\Notification\MessageTargetEmail;
use BetaKiller\Notification\MessageTargetInterface;
use BetaKiller\Notification\TransportInterface;
use Database;
use DateTimeImmutable;
use DB;

class NotificationLog extends \ORM implements NotificationLogInterface
{
    public const COL_ID           = 'id';
    public const COL_HASH         = 'hash';
    public const COL_USER_ID      = 'user_id';
    public const COL_MESSAGE_NAME = 'name';
    public const COL_TARGET       = 'target';
    public const COL_PROCESSED_AT = 'processed_at';
    public const COL_STATUS       = 'status';
    public const COL_TRANSPORT    = 'transport';
    public const COL_LANG         = 'lang';
    public const COL_SUBJ         = 'subject';
    public const COL_BODY         = 'body';
    public const COL_RESULT       = 'result';
    public const COL_READ_AT      = 'read_at';

    public const STATUS_SUCCEEDED = 'succeeded';
    public const STATUS_FAILED    = 'failed';

    public const MAX_LENGTH_CODENAME = 64;

    private static bool $tablesChecked = false;

    /**
     * Custom configuration (set table name, configure relations, load_with(), etc)
     */
    protected function configure(): void
    {
        $this->_db_group   = 'notifications';
        $this->_table_name = 'notification_log';

        $this->initSqliteDB();
    }

    protected function initSqliteDB(): void
    {
        if (!static::$tablesChecked) {
            $this->enableAutoVacuum();
            $this->createTableIfNotExists();
            static::$tablesChecked = true;
        }
    }

    protected function createTableIfNotExists(): void
    {
        DB::query(Database::SELECT, 'CREATE TABLE IF NOT EXISTS `notification_log` (
    id INTEGER PRIMARY KEY NOT NULL,
    hash VARCHAR(128) NOT NULL,
    name VARCHAR(64) NOT NULL,
    user_id INTEGER NULL DEFAULT NULL,
    target VARCHAR(128) NOT NULL,
    lang VARCHAR(2) NOT NULL,
    processed_at DATETIME NOT NULL,
    status VARCHAR(16) NOT NULL,
    transport VARCHAR(16) NOT NULL,
    subject VARCHAR(255) NULL DEFAULT NULL,
    body TEXT NULL DEFAULT NULL,
    result TEXT NULL DEFAULT NULL,
    read_at DATETIME DEFAULT NULL
)')->execute($this->_db_group);
    }

    private function enableAutoVacuum(): void
    {
        DB::query(Database::SELECT, 'PRAGMA auto_vacuum = FULL')->execute($this->_db_group);
    }

    public function setProcessedAt(DateTimeImmutable $value): NotificationLogInterface
    {
        $this->set_datetime_column_value(self::COL_PROCESSED_AT, $value);

        return $this;
    }

    public function setMessageName(string $messageName): NotificationLogInterface
    {
        $this->set(self::COL_MESSAGE_NAME, $messageName);

        return $this;
    }

    public function setTarget(MessageTargetInterface $target): NotificationLogInterface
    {
        $this->set(self::COL_TARGET, $this->makeTargetString($target));

        if ($target instanceof UserInterface) {
            $this->set(self::COL_USER_ID, $target->getID());
        }

        return $this;
    }

    /**
     * Returns linked user ID if exists
     *
     * @return string|null
     */
    public function getTargetUserId(): ?string
    {
        return $this->get(self::COL_USER_ID);
    }

    public function setTransport(TransportInterface $transport): NotificationLogInterface
    {
        $this->set(self::COL_TRANSPORT, $transport->getName());

        return $this;
    }

    public function setSubject(string $subj): NotificationLogInterface
    {
        $this->set(self::COL_SUBJ, $subj);

        return $this;
    }

    /**
     * @return string
     */
    public function getSubject(): ?string
    {
        return $this->get(self::COL_SUBJ);
    }

    public function setBody(string $body): NotificationLogInterface
    {
        $this->set(self::COL_BODY, $body);

        return $this;
    }

    public function markAsSucceeded(): NotificationLogInterface
    {
        return $this->setStatus(self::STATUS_SUCCEEDED);
    }

    public function markAsFailed(string $result = null): NotificationLogInterface
    {
        if ($result) {
            $this->set(self::COL_RESULT, $result);
        }

        return $this->setStatus(self::STATUS_FAILED);
    }

    /**
     * @return string|null
     */
    public function getFailureReason(): ?string
    {
        return $this->get(self::COL_RESULT);
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getProcessedAt(): DateTimeImmutable
    {
        return $this->get_datetime_column_value(self::COL_PROCESSED_AT);
    }

    /**
     * @return string
     */
    public function getMessageName(): string
    {
        return (string)$this->get(self::COL_MESSAGE_NAME);
    }

    /**
     * @return \BetaKiller\Notification\MessageTargetInterface
     * @throws \Kohana_Exception
     */
    public function getTargetString(): string
    {
        return (string)$this->get(self::COL_TARGET);
    }

    /**
     * @return string
     */
    public function getTransportName(): string
    {
        return (string)$this->get(self::COL_TRANSPORT);
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return (string)$this->get(self::COL_BODY);
    }

    /**
     * @inheritDoc
     */
    public function getStatus(): string
    {
        return (string)$this->get(self::COL_STATUS);
    }

    public function isSucceeded(): bool
    {
        return $this->getStatus() === self::STATUS_SUCCEEDED;
    }

    private function makeTargetString(MessageTargetInterface $target): string
    {
        if ($target instanceof MessageTargetEmail) {
            return sprintf('%s <%s>', $target->getFullName(), $target->getEmail());
        }

        if ($target instanceof UserInterface) {
            return sprintf('%s <%s>', $target->getFullName(), $target->getEmail());
        }

        throw new DomainException('Unknown target type ":type"', [
            ':type' => \gettype($target),
        ]);
    }

    private function setStatus(string $value): NotificationLogInterface
    {
        $this->set(self::COL_STATUS, $value);

        return $this;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return (string)$this->get(self::COL_HASH);
    }

    /**
     * @inheritDoc
     */
    public function setHash(string $value): NotificationLogInterface
    {
        $this->set(self::COL_HASH, $value);

        return $this;
    }

    /**
     * @param string $isoCode
     *
     * @return \BetaKiller\Model\NotificationLogInterface
     */
    public function setLanguageIsoCode(string $isoCode): NotificationLogInterface
    {
        $this->set(self::COL_LANG, $isoCode);

        return $this;
    }

    /**
     * @return string
     */
    public function getLanguageIsoCode(): string
    {
        return (string)$this->get(self::COL_LANG);
    }

    /**
     * @inheritDoc
     */
    public function markAsRead(): void
    {
        $this->set_datetime_column_value(self::COL_READ_AT, new DateTimeImmutable("now"));
    }

    /**
     * @inheritDoc
     */
    public function isRead(): bool
    {
        return $this->get(self::COL_READ_AT) !== null;
    }

    /**
     * @inheritDoc
     */
    public function getReadAt(): DateTimeImmutable
    {
        return $this->get_datetime_column_value(self::COL_READ_AT);
    }

    /**
     * @inheritDoc
     */
    public function isRetryAvailable(): bool
    {
        return !$this->isSucceeded()
            && $this->getTargetUserId()
            && $this->getBody();
    }
}
