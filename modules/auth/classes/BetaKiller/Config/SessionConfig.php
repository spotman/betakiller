<?php
declare(strict_types=1);

namespace BetaKiller\Config;

use BetaKiller\Config\AbstractConfig;
use BetaKiller\Exception;
use DateInterval;

class SessionConfig extends AbstractConfig implements SessionConfigInterface
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

    public function getEncryptionKey(): ?string
    {
        return $this->get(['encrypt_key']) ?: null;
    }

    protected function getConfigRootGroup(): string
    {
        return 'session';
    }
}
