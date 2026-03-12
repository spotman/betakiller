<?php

declare(strict_types=1);

namespace BetaKiller\Config;

use BetaKiller\Exception;
use DateInterval;

class SessionConfig extends AbstractConfig implements SessionConfigInterface
{
    public const HASH_KEY    = 'hash_key';
    public const HASH_METHOD = 'hash_method';
    public const LIFETIME    = 'lifetime';
    public const ENCRYPT_KEY = 'encrypt_key';
    public const BIND_TO_UA  = 'bind_to_ua';

    public function getHashKey(): string
    {
        return (string)$this->get([self::HASH_KEY]);
    }

    public function getHashMethod(): string
    {
        return (string)$this->get([self::HASH_METHOD]);
    }

    public function getLifetime(): DateInterval
    {
        $seconds = (int)$this->get([self::LIFETIME]);

        if (!$seconds) {
            throw new Exception('Session lifetime must be configured');
        }

        return new DateInterval('PT'.$seconds.'S');
    }

    public function getEncryptionKey(): ?string
    {
        return $this->get([self::ENCRYPT_KEY]) ?: null;
    }

    /**
     * @inheritDoc
     */
    public function isBoundToUserAgent(): bool
    {
        return (bool)$this->get([self::BIND_TO_UA]);
    }

    protected function getConfigRootGroup(): string
    {
        return 'session';
    }
}
