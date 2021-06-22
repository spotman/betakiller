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
    public function getClientHost(): string;

    /**
     * @return string
     */
    public function getClientPort(): string;

    /**
     * @return bool
     */
    public function hasServerHost(): bool;

    /**
     * @return string
     */
    public function getServerHost(): string;

    /**
     * @return string
     */
    public function getServerPort(): string;
}
