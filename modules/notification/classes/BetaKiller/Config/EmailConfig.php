<?php
declare(strict_types=1);

namespace BetaKiller\Config;

class EmailConfig extends AbstractConfig implements EmailConfigInterface
{
    public const CONFIG_GROUP_NAME = 'email';
    public const KEY_OPTIONS       = 'options';
    public const KEY_FROM          = 'from';
    public const PATH_FROM_NAME    = [self::KEY_FROM, 'name'];
    public const PATH_FROM_EMAIL   = [self::KEY_FROM, 'email'];
    public const PATH_DOMAIN       = [self::KEY_OPTIONS, 'domain'];
    public const PATH_HOST         = [self::KEY_OPTIONS, 'hostname'];
    public const PATH_PORT         = [self::KEY_OPTIONS, 'port'];
    public const PATH_USERNAME     = [self::KEY_OPTIONS, 'username'];
    public const PATH_PASSWORD     = [self::KEY_OPTIONS, 'password'];
    public const PATH_ENCRYPTION   = [self::KEY_OPTIONS, 'encryption'];
    public const PATH_TIMEOUT      = [self::KEY_OPTIONS, 'timeout'];

    /**
     * @return string
     */
    protected function getConfigRootGroup(): string
    {
        return self::CONFIG_GROUP_NAME;
    }

    public function getHost(): string
    {
        return (string)$this->get(self::PATH_HOST);
    }

    public function getPort(): int
    {
        return (int)$this->get(self::PATH_PORT);
    }

    public function useEncryption(): bool
    {
        return (bool)$this->get(self::PATH_ENCRYPTION, true);
    }

    public function getUsername(): ?string
    {
        return $this->get(self::PATH_USERNAME, true);
    }

    public function getPassword(): ?string
    {
        return $this->get(self::PATH_PASSWORD, true);
    }

    public function getTimeout(): int
    {
        return $this->get(self::PATH_TIMEOUT, true) ?? 5;
    }

    public function getDomain(): string
    {
        return $this->get(self::PATH_DOMAIN);
    }

    public function getFromEmail(): string
    {
        return $this->get(self::PATH_FROM_EMAIL);
    }

    public function getFromName(): string
    {
        return $this->get(self::PATH_FROM_NAME);
    }
}
