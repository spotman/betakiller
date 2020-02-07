<?php
declare(strict_types=1);

namespace BetaKiller\Security;

interface SecurityConfigInterface
{
    /**
     * @return bool
     */
    public function isCspEnabled(): bool;

    /**
     * @return bool
     */
    public function isCspSafeModeEnabled(): bool;

    /**
     * @return bool
     */
    public function isHstsEnabled(): bool;

    /**
     * @return int
     */
    public function getHstsMaxAge(): int;

    /**
     * @return bool
     */
    public function isHstsForSubdomains(): bool;

    /**
     * @return bool
     */
    public function isHstsPreload(): bool;

    /**
     * @return bool
     */
    public function isErrorLogEnabled(): bool;

    /**
     * @return string[][]
     */
    public function getCspRules(): array;

    /**
     * @return string[]
     */
    public function getHeadersToAdd(): array;

    /**
     * @return string[]
     */
    public function getHeadersToRemove(): array;
}
