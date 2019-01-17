<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use BetaKiller\Exception\DomainException;
use BetaKiller\Notification\NotificationTargetEmail;
use BetaKiller\Notification\NotificationTargetInterface;
use BetaKiller\Notification\NotificationTransportInterface;
use DateTimeImmutable;

class NotificationLog extends \ORM implements NotificationLogInterface
{
    public const TABLE_COLUMN_ID           = 'id';
    public const TABLE_COLUMN_HASH         = 'hash';
    public const TABLE_COLUMN_USER_ID      = 'user_id';
    public const TABLE_COLUMN_MESSAGE_NAME = 'name';
    public const TABLE_COLUMN_TARGET       = 'target';
    public const TABLE_COLUMN_PROCESSED_AT = 'processed_at';
    public const TABLE_COLUMN_STATUS       = 'status';
    public const TABLE_COLUMN_TRANSPORT    = 'transport';
    public const TABLE_COLUMN_LANG         = 'lang';
    public const TABLE_COLUMN_SUBJ         = 'subject';
    public const TABLE_COLUMN_BODY         = 'body';
    public const TABLE_COLUMN_RESULT       = 'result';

    public const STATUS_SUCCEEDED = 'succeeded';
    public const STATUS_FAILED    = 'failed';

    /**
     * Custom configuration (set table name, configure relations, load_with(), etc)
     */
    protected function configure(): void
    {
        $this->_table_name = 'notification_log';

        $this->belongs_to([
            'user' => [
                'model'       => 'User',
                'foreign_key' => 'user_id',
            ],
        ]);

        $this->load_with(['user']);
    }

    public function setProcessedAt(DateTimeImmutable $value): NotificationLogInterface
    {
        $this->set_datetime_column_value(self::TABLE_COLUMN_PROCESSED_AT, $value);

        return $this;
    }

    public function setMessageName(string $messageName): NotificationLogInterface
    {
        $this->set(self::TABLE_COLUMN_MESSAGE_NAME, $messageName);

        return $this;
    }

    public function setTarget(NotificationTargetInterface $target): NotificationLogInterface
    {
        $this->set(self::TABLE_COLUMN_TARGET, $this->makeTargetString($target));

        if ($target instanceof UserInterface) {
            $this->set(self::TABLE_COLUMN_USER_ID, $target->getID());
        }

        return $this;
    }

    /**
     * Returns linked user if exists
     *
     * @return \BetaKiller\Model\UserInterface|null
     */
    public function getTargetUser(): ?UserInterface
    {
        return $this->getRelatedEntity('user', true);
    }

    public function setTransport(NotificationTransportInterface $transport): NotificationLogInterface
    {
        $this->set(self::TABLE_COLUMN_TRANSPORT, $transport->getName());

        return $this;
    }

    public function setSubject(string $subj): NotificationLogInterface
    {
        $this->set(self::TABLE_COLUMN_SUBJ, $subj);

        return $this;
    }

    /**
     * @return string
     */
    public function getSubject(): ?string
    {
        return $this->get(self::TABLE_COLUMN_SUBJ);
    }

    public function setBody(string $body): NotificationLogInterface
    {
        $this->set(self::TABLE_COLUMN_BODY, $body);

        return $this;
    }

    public function markAsSucceeded(): NotificationLogInterface
    {
        return $this->setStatus(self::STATUS_SUCCEEDED);
    }

    public function markAsFailed(string $result = null): NotificationLogInterface
    {
        if ($result) {
            $this->set(self::TABLE_COLUMN_RESULT, $result);
        }

        return $this->setStatus(self::STATUS_FAILED);
    }

    /**
     * @return string|null
     */
    public function getFailureReason(): ?string
    {
        return $this->get(self::TABLE_COLUMN_RESULT);
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getProcessedAt(): DateTimeImmutable
    {
        return $this->get_datetime_column_value(self::TABLE_COLUMN_PROCESSED_AT);
    }

    /**
     * @return string
     */
    public function getMessageName(): string
    {
        return (string)$this->get(self::TABLE_COLUMN_MESSAGE_NAME);
    }

    /**
     * @return \BetaKiller\Notification\NotificationTargetInterface
     * @throws \Kohana_Exception
     */
    public function getTargetString(): string
    {
        return (string)$this->get(self::TABLE_COLUMN_TARGET);
    }

    /**
     * @return string
     */
    public function getTransportName(): string
    {
        return (string)$this->get(self::TABLE_COLUMN_TRANSPORT);
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return (string)$this->get(self::TABLE_COLUMN_BODY);
    }

    public function isSucceeded(): bool
    {
        return $this->get(self::TABLE_COLUMN_STATUS) === self::STATUS_SUCCEEDED;
    }

    private function makeTargetString(NotificationTargetInterface $target): string
    {
        if ($target instanceof NotificationTargetEmail) {
            return sprintf('%s <%s>', $target->getFullName(), $target->getEmail());
        }

        if ($target instanceof UserInterface) {
            return sprintf('%s <%s> aka %s', $target->getFullName(), $target->getEmail(), $target->getUsername());
        }

        throw new DomainException('Unknown target type ":type"', [
            ':type' => \gettype($target),
        ]);
    }

    private function setStatus(string $value): NotificationLogInterface
    {
        $this->set(self::TABLE_COLUMN_STATUS, $value);

        return $this;
    }

    public function getUser(): ?UserInterface
    {
        /** @var \BetaKiller\Model\User $relation */
        $relation = $this->get('user');

        return $relation->loaded() ? $relation : null;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return (string)$this->get(self::TABLE_COLUMN_HASH);
    }

    /**
     * @param string $value
     *
     * @return mixed
     */
    public function setHash(string $value): NotificationLogInterface
    {
        $this->set(self::TABLE_COLUMN_HASH, $value);

        return $this;
    }

    /**
     * @param string $isoCode
     *
     * @return \BetaKiller\Model\NotificationLogInterface
     */
    public function setLanguageIsoCode(string $isoCode): NotificationLogInterface
    {
        $this->set(self::TABLE_COLUMN_LANG, $isoCode);

        return $this;
    }

    /**
     * @return string
     */
    public function getLanguageIsoCode(): string
    {
        return (string)$this->get(self::TABLE_COLUMN_LANG);
    }
}
