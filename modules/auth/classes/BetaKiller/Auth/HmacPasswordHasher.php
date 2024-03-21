<?php
declare(strict_types=1);

namespace BetaKiller\Auth;

use BetaKiller\Config\SessionConfigInterface;

/**
 * Perform a hmac hash, using the configured method.
 */
final readonly class HmacPasswordHasher implements PasswordHasherInterface
{
    public function __construct(private SessionConfigInterface $config)
    {
    }

    public function proceed(string $password): string
    {
        return hash_hmac($this->config->getHashMethod(), $password, $this->config->getHashKey());
    }
}
