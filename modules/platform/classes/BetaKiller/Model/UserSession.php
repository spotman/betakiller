<?php

declare(strict_types=1);

namespace BetaKiller\Model;

use BetaKiller\Exception\DomainException;
use BetaKiller\Session\SessionCause;
use DateInterval;
use DateTimeImmutable;

class UserSession extends AbstractCreatedAt implements UserSessionInterface
{
    public const TOKEN_LENGTH = 40;

    public const COL_TOKEN          = 'token';
    public const COL_USER_ID        = 'user_id';
    public const COL_CONTENTS       = 'contents';
    public const COL_LAST_ACTIVE_AT = 'last_active_at';
    public const COL_IS_REGENERATED = 'is_regenerated';
    public const COL_CAUSE          = 'cause';

    public const REL_USER = 'user';

    /**
     * Custom configuration (set table name, configure relations, load_with(), etc)
     */
    protected function configure(): void
    {
        $this->_table_name  = 'sessions';
        $this->_primary_key = 'token';

        $this->belongs_to([
            self::REL_USER => [
                'model'       => User::getModelName(),
                'foreign_key' => self::COL_USER_ID,
            ],
        ]);

        // Speedup a bit
//        $this->load_with([self::REL_USER]);
    }

    public function setToken(string $value): UserSessionInterface
    {
        $this->set(self::COL_TOKEN, $value);

        return $this;
    }

    public function getToken(): string
    {
        return $this->get(self::COL_TOKEN);
    }

    public function getLastActiveAt(): DateTimeImmutable
    {
        return $this->get_datetime_column_value(self::COL_LAST_ACTIVE_AT);
    }

    public function setLastActiveAt(DateTimeImmutable $value): UserSessionInterface
    {
        $this->set_datetime_column_value(self::COL_LAST_ACTIVE_AT, $value);

        return $this;
    }

    public function setUser(UserInterface $user): UserSessionInterface
    {
        if ($user instanceof GuestUserInterface) {
            throw new DomainException('Can not link session :id to a guest user', [':id' => $this->getID()]);
        }

        $this->set(self::REL_USER, $user);

        return $this;
    }

    public function setUserID(string $id): UserSessionInterface
    {
        $this->set(self::COL_USER_ID, $id);

        return $this;
    }

    public function hasUser(): bool
    {
        return (bool)$this->get(self::COL_USER_ID);
    }

    public function getUser(): UserInterface
    {
        return $this->fetchRelatedEntity(self::REL_USER, true);
    }

    public function getUserId(): string
    {
        return $this->get(self::COL_USER_ID);
    }

    public function isExpiredIn(DateInterval $interval): bool
    {
        return $this->getLastActiveAt() < (new DateTimeImmutable())->sub($interval);
    }

    public function getContents(): string
    {
        return (string)$this->get(self::COL_CONTENTS);
    }

    public function setContents(string $value): UserSessionInterface
    {
        $this->set(self::COL_CONTENTS, $value);

        return $this;
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

    public function setCause(SessionCause $value): UserSessionInterface
    {
        $this->setOnce(self::COL_CAUSE, $value->getCodename());

        return $this;
    }

    public function hasCause(): bool
    {
        return !empty($this->get(self::COL_CAUSE));
    }

    public function getCause(): SessionCause
    {
        $raw = $this->get(self::COL_CAUSE);

        return SessionCause::fromCodename($raw);
    }
}
