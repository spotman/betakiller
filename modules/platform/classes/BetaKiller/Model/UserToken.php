<?php
declare(strict_types=1);

namespace BetaKiller\Model;

class UserToken extends \ORM
{
    public const TOKEN_LENGTH = 32;

    /**
     * Custom configuration (set table name, configure relations, load_with(), etc)
     */
    protected function configure(): void
    {
        $this->_table_name = 'user_tokens';

        $this->belongs_to([
            'user' => [
                'model' => 'User',
            ],
        ]);

        $this->_created_column = [
            'column' => 'created',
            'format' => true,
        ];
    }

    public function setToken(string $value): UserToken
    {
        $this->set('token', $value);

        return $this;
    }

    public function getToken(): string
    {
        return $this->get('token');
    }

    public function getUserAgentHash(): string
    {
        return $this->get('user_agent');
    }

    public function setUserAgent(string $value): UserToken
    {
        $this->set('user_agent', $this->hashUserAgent($value));

        return $this;
    }

    public function isValidUserAgent(string $value): bool
    {
        return $this->getUserAgentHash() === $this->hashUserAgent($value);
    }

    public function getCreated(): int
    {
        return (int)$this->get('created');
    }

    public function getExpires(): int
    {
        return (int)$this->get('expires');
    }

    public function setExpires(int $value): UserToken
    {
        $this->set('expires', $value);

        return $this;
    }

    public function setUser(UserInterface $user): UserToken
    {
        $this->set('user', $user);

        return $this;
    }

    public function getUser(): UserInterface
    {
        return $this->getRelatedEntity('user');
    }

    public function isExpired(): bool
    {
        return $this->getExpires() < time();
    }

    private function hashUserAgent(string $value): string
    {
        return \sha1($value);
    }
}
