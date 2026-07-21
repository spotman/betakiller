<?php

declare(strict_types=1);

namespace BetaKiller\Config;

use DateInterval;
use Dflydev\FigCookies\Modifier\SameSite;

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

    /**
     * @return bool
     */
    public function isBoundToUserAgent(): bool;

    /**
     * @return \Dflydev\FigCookies\Modifier\SameSite|null
     */
    public function getCookieSameSite(): ?SameSite;
}
