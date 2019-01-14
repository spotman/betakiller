<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use BetaKiller\Exception\DomainException;
use DateInterval;
use DateTimeImmutable;

class UserSession extends \ORM
{
    public const TOKEN_LENGTH = 32;

    /**
     * Custom configuration (set table name, configure relations, load_with(), etc)
     */
    protected function configure(): void
    {
        $this->_table_name  = 'sessions';
        $this->_primary_key = 'token';

        $this->belongs_to([
            'user' => [
                'model'       => 'User',
                'foreign_key' => 'user_id',
            ],
        ]);

        $this->load_with(['user']);
    }

    public function setToken(string $value): UserSession
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

    public function setCreatedAt(DateTimeImmutable $value): UserSession
    {
        $this->set_datetime_column_value('created_at', $value);

        return $this;
    }

    public function getLastActiveAt(): DateTimeImmutable
    {
        return $this->get_datetime_column_value('last_active_at');
    }

    public function setLastActiveAt(DateTimeImmutable $value): UserSession
    {
        $this->set_datetime_column_value('last_active_at', $value);

        return $this;
    }

    public function setUser(UserInterface $user): UserSession
    {
        if ($user instanceof GuestUserInterface) {
            throw new DomainException('Can not link session :id to a guest user', [':id' => $this->getID()]);
        }

        $this->set('user', $user);

        return $this;
    }

    public function setUserID(string $id): UserSession
    {
        $this->set('user_id', $id);

        return $this;
    }

    public function hasUser(): bool
    {
        return (bool)$this->getUser();
    }

    public function getUser(): ?UserInterface
    {
        /** @var User $user */
        $user = $this->get('user');

        return $user->loaded() ? $user : null;
    }

    public function isExpiredIn(DateInterval $interval): bool
    {
        return $this->getCreatedAt()->add($interval)->getTimestamp() < time();
    }

    public function getContents(): string
    {
        return (string)$this->get('contents');
    }

    public function setContents(string $value): UserSession
    {
        $this->set('contents', $value);

        return $this;
    }

    public function setOrigin(string $url): UserSession
    {
        $this->set('origin', $url);

        return $this;
    }

    public function getOrigin(): string
    {
        return $this->get('origin');
    }
}
