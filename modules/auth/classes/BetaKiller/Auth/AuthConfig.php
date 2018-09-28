<?php
declare(strict_types=1);

namespace BetaKiller\Auth;

use BetaKiller\Config\AbstractConfig;

class AuthConfig extends AbstractConfig
{
    public function getHashKey(): string
    {
        return (string)$this->get(['hash_key']);
    }

    public function getHashMethod(): string
    {
        return (string)$this->get(['hash_method']);
    }

    public function getLifetime(): int
    {
        return (int)$this->get(['lifetime']);
    }

    public function getSessionKey(): string
    {
        return (string)$this->get(['session_key']);
    }

    protected function getConfigRootGroup(): string
    {
        return 'auth';
    }
}
