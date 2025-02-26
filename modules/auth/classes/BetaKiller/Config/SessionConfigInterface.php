<?php
declare(strict_types=1);

namespace BetaKiller\Config;

use DateInterval;

interface SessionConfigInterface
{
    /**
     * @return string
     */
    public function getHashKey(): string;

    /**
     * @return string
     */
    public function getHashMethod(): string;

    /**
     * @return \DateInterval
     */
    public function getLifetime(): DateInterval;

    /**
     * @return string|null
     */
    public function getEncryptionKey(): ?string;
}
