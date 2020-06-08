<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use BetaKiller\Exception\DomainException;
use DateInterval;
use DateTimeImmutable;

class UserSession extends \ORM implements UserSessionInterface
{
    public const TOKEN_LENGTH = 32;

    public const COL_IS_REGENERATED = 'is_regenerated';

    /**
     * Custom configuration (set table name, configure relations, load_with(), etc)
     */
    protected function configure(): void
    {
        $this->_table_name  = 'sessions';
        $this->_primary_key = 'token';

        $this->belongs_to([
            'user' => [
                'model'       => User::getModelName(),
                'foreign_key' => 'user_id',
            ],
        ]);

        $this->load_with(['user']);
    }

    public function setToken(string $value): UserSessionInterface
    {
        $this->set('token', $value);

        return $this;
    }

    public function getToken(): string
    {
        return $this->get('token');
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->get_datetime_column_value('created_at');
    }

    public function setCreatedAt(DateTimeImmutable $value): UserSessionInterface
    {
        $this->set_datetime_column_value('created_at', $value);

        return $this;
    }

    public function getLastActiveAt(): DateTimeImmutable
    {
        return $this->get_datetime_column_value('last_active_at');
    }

    public function setLastActiveAt(DateTimeImmutable $value): UserSessionInterface
    {
        $this->set_datetime_column_value('last_active_at', $value);

        return $this;
    }

    public function setUser(UserInterface $user): UserSessionInterface
    {
        if ($user instanceof GuestUserInterface) {
            throw new DomainException('Can not link session :id to a guest user', [':id' => $this->getID()]);
        }

        $this->set('user', $user);

        return $this;
    }

    public function setUserID(string $id): UserSessionInterface
    {
        $this->set('user_id', $id);

        return $this;
    }

    public function hasUser(): bool
    {
        return (bool)$this->get('user_id');
    }

    public function getUser(): ?UserInterface
    {
        return $this->getRelatedEntity('user', true);
    }

    public function isExpiredIn(DateInterval $interval): bool
    {
        return $this->getCreatedAt()->add($interval)->getTimestamp() < time();
    }

    public function getContents(): string
    {
        return (string)$this->get('contents');
    }

    public function setContents(string $value): UserSessionInterface
    {
        $this->set('contents', $value);

        return $this;
    }

    public function setOrigin(string $url): UserSessionInterface
    {
        $this->set('origin', $url);

        return $this;
    }

    public function getOrigin(): string
    {
        return (string)$this->get('origin');
    }

    public function markAsRegenerated(): void
    {
        $this->set(self::COL_IS_REGENERATED, true);
    }

    /**
     * @inheritDoc
     */
    public function isRegenerated(): bool
    {
        return (bool)$this->get(self::COL_IS_REGENERATED);
    }
}
