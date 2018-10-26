<?php
declare(strict_types=1);

namespace BetaKiller\Auth;

use BetaKiller\Config\AbstractConfig;
use BetaKiller\Exception;
use DateInterval;

class SessionConfig extends AbstractConfig
{
    public function getHashKey(): string
    {
        return (string)$this->get(['hash_key']);
    }

    public function getHashMethod(): string
    {
        return (string)$this->get(['hash_method']);
    }

    public function getLifetime(): DateInterval
    {
        $seconds = (int)$this->get(['lifetime']);

        if (!$seconds) {
            throw new Exception('Session lifetime must be configured');
        }

        return new DateInterval('PT'.$seconds.'S');
    }

    public function getAllowedClassNames(): array
    {
        return (array)$this->get(['allowed_class_names'], true);
    }

    public function getEncryptionKey(): string
    {
        return (string)$this->get(['encrypt_key']);
    }

    protected function getConfigRootGroup(): string
    {
        return 'session';
    }
}
