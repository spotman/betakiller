<?php
declare(strict_types=1);

namespace BetaKiller\Config;

interface WampConfigInterface
{
    /**
     * @return string
     */
    public function getExternalRealmName(): string;

    /**
     * @return string
     */
    public function getInternalRealmName(): string;

    /**
     * @return string[]
     */
    public function getAllRealms(): array;

    /**
     * @return string
     */
    public function getConnectionHost(): string;

    /**
     * @return string
     */
    public function getConnectionPort(): string;
}
