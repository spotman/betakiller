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
     * @return string[][]
     */
    public function getCspRules(): array;
}
